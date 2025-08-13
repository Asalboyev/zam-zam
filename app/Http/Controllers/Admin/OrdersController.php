<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\DailyMeal;
use App\Models\Meal;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\BalanceHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;


class OrdersController extends Controller
{


// OrderController.php
    public function updatePaymentMethod(Request $request, Order $order)
    {
        $request->validate([
            'payment_method' => 'required|in:naqt,karta'
        ]);

        $order->payment_method = $request->payment_method;
        $order->save();

        return response()->json(['success' => true]);
    }


    public function updateReceivedAmount(Request $request, Order $order)
    {
        $request->validate([
            'received_amount' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $oldAmount = $order->received_amount;
            $newAmount = (int)$request->received_amount;
            $diff = $newAmount - $oldAmount; // qancha o‘zgarish bo‘ldi

            $order->received_amount = $newAmount;
            $order->save();

            // Customer balansini o‘zgartirish
            $customer = $order->customer;
            $customer->balance += $diff; // diff musbat bo‘lsa qarz kamayadi, manfiy bo‘lsa qarz oshadi
            $customer->save();

            // Tarixga yozish
            \App\Models\BalanceHistory::create([
                'customer_id' => $customer->id,
                'amount' => $diff,
                'type' => 'payment',
                'description' => "Order #{$order->id} uchun to‘lov miqdori o‘zgartirildi: {$oldAmount} → {$newAmount}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'total_amount' => $order->total_amount,
                'customer_balance' => $customer->balance,
                'customer_type' => $customer->type
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Xatolik: ' . $e->getMessage()
            ], 500);
        }
    }

    public function monthly_debtors(Request $request)
    {
        $date = $request->input('order_date', now()->format('Y-m-d'));

        $drivers = Driver::where('is_active', true)->get();

        // 1. Oylik turdagi va manfiy balansga ega mijozlarni olamiz,
        //    ularning so‘nggi buyurtmasi va haydovchisi bilan
        $customers = Customer::with(['lastOrder.driver'])
            ->where('type', 'oylik')
            ->where('balance', '<', 0)
            ->get();

        // 2. Har bir mijoz uchun ularning barcha buyurtmalari emas, balki
        //    faqat so‘nggi buyurtmasini (lastOrder) viewda ishlatamiz,
        //    shuning uchun alohida $latestOrders kerak emas.

        // 3. Buyurtmalar asosida statistikalar kerak bo‘lsa,
        //    barchasini olish uchun:

        $allOrders = Order::whereIn('customer_id', $customers->pluck('id'))
            ->where('order_date', $date)
            ->get();

        // 4. Hisoblashlar va guruhlashlar

        $customerTotal = $allOrders->sum('total_amount');
        $driverTotal = $allOrders->sum('received_amount');

        $planByPaymentType = $allOrders->groupBy('payment_type')->map(fn($g) => $g->sum('total_amount'));
        $factByPaymentType = $allOrders->groupBy('payment_type')->map(fn($g) => $g->sum('received_amount'));

        $planByMethod = $allOrders->groupBy('payment_method')->map(fn($g) => $g->sum('total_amount'));
        $factByMethod = $allOrders->groupBy('payment_method')->map(fn($g) => $g->sum('received_amount'));

        $dailyMeals = DailyMeal::with('items')->where('date', $date)->get();

        $meals = $dailyMeals->flatMap(fn($dailyMeal) => $dailyMeal->items)
            ->groupBy('id')
            ->map(function ($groupedItems) {
                $meal = $groupedItems->first();
                $totalCount = $groupedItems->sum(fn($item) => $item->pivot->count ?? 0);
                $meal->total_count = $totalCount;
                return $meal;
            })->values();

        $mealStats = [];
        foreach ($dailyMeals as $dailyMeal) {
            foreach ($dailyMeal->items as $item) {
                $orderedCount = $allOrders->filter(function ($order) use ($item) {
                    return in_array($item->id, [
                        $order->meal_1_id,
                        $order->meal_2_id,
                        $order->meal_3_id,
                        $order->meal_4_id
                    ]);
                })->count();

                if (!isset($mealStats[$item->id])) {
                    $mealStats[$item->id] = [
                        'meal_name' => $item->name,
                        'initial_count' => $item->pivot->count,
                        'ordered_count' => $orderedCount,
                        'remaining' => $item->pivot->count - $orderedCount,
                    ];
                } else {
                    $mealStats[$item->id]['initial_count'] += $item->pivot->count;
                    $mealStats[$item->id]['ordered_count'] += $orderedCount;
                    $mealStats[$item->id]['remaining'] = $mealStats[$item->id]['initial_count'] - $mealStats[$item->id]['ordered_count'];
                }
            }
        }

        return view('admin.orders.monthly_debtors', compact(
            'date',
            'customers',      // Bu yerda mijozlar bitta marta chiqadi
            'drivers',
            'dailyMeals',
            'meals',
            'mealStats',
            'allOrders',      // Barcha orderlar
            'customerTotal',
            'driverTotal',
            'planByPaymentType',
            'factByPaymentType',
            'planByMethod',
            'factByMethod'
        ));
    }

    public function ordinary_debt(Request $request)
    {
        $date = $request->input('order_date', now()->format('Y-m-d'));

        $customers = Customer::with(['lastOrder.driver'])->get();
        $drivers = Driver::where('is_active', true)->get();

        // Bugungi orderlar, faqat customer.balance < 0 va to'liq to'lanmagan buyurtmalar
        $latestOrders = Order::with(['customer', 'meal1', 'meal2', 'meal3', 'meal4', 'driver'])
            ->whereHas('customer', function ($query) {
                $query->where('balance', '<', 0)
                    ->where('type', 'odiy'); // faqat 'odiy' tipidagi mijozlar
            })
            ->whereColumn('total_amount', '>', 'received_amount') // faqat to'liq to'lanmagan buyurtmalar
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        // Keyingi kodlar o'zgarishsiz qoladi...

        // Umumiy mijoz va haydovchi summalari
        $customerTotal = $latestOrders->sum('total_amount');
        $driverTotal = $latestOrders->sum('received_amount');

        // Plan - payment_type bo‘yicha
        $planByPaymentType = $latestOrders->groupBy('payment_type')->map(function ($group) {
            return $group->sum('total_amount');
        });

        // Fakt - payment_type bo‘yicha
        $factByPaymentType = $latestOrders->groupBy('payment_type')->map(function ($group) {
            return $group->sum('received_amount');
        });

        // Plan (payment_method bo‘yicha)
        $planByMethod = $latestOrders->groupBy('payment_method')->map(function ($group) {
            return $group->sum('total_amount');
        });

        // Fakt (payment_method bo‘yicha)
        $factByMethod = $latestOrders->groupBy('payment_method')->map(function ($group) {
            return $group->sum('received_amount');
        });

        // Bugungi DailyMeal'lar (itemlar bilan birga)
        $dailyMeals = DailyMeal::with('items')->where('date', $date)->get();

        // Barcha itemlar kolleksiyasi
        $meals = $dailyMeals->flatMap(function ($dailyMeal) {
            return $dailyMeal->items;
        })->groupBy('id')->map(function ($groupedItems) {
            $meal = $groupedItems->first();
            $totalCount = $groupedItems->sum(function ($item) {
                return $item->pivot->count ?? 0;
            });

            $meal->total_count = $totalCount;
            return $meal;
        })->values();

        // Har bir ovqat bo‘yicha statistikani tayyorlash
        $mealStats = [];

        foreach ($dailyMeals as $dailyMeal) {
            foreach ($dailyMeal->items as $item) {
                $orderedCount = $latestOrders->filter(function ($order) use ($item) {
                    return in_array($item->id, [
                        $order->meal_1_id,
                        $order->meal_2_id,
                        $order->meal_3_id,
                        $order->meal_4_id
                    ]);
                })->count();

                if (!isset($mealStats[$item->id])) {
                    $mealStats[$item->id] = [
                        'meal_name' => $item->name,
                        'initial_count' => $item->pivot->count,
                        'ordered_count' => $orderedCount,
                        'remaining' => $item->pivot->count - $orderedCount,
                    ];
                } else {
                    $mealStats[$item->id]['initial_count'] += $item->pivot->count;
                    $mealStats[$item->id]['ordered_count'] += $orderedCount;
                    $mealStats[$item->id]['remaining'] = $mealStats[$item->id]['initial_count'] - $mealStats[$item->id]['ordered_count'];
                }
            }
        }

        return view('admin.orders.ordinary_debt', compact(
            'date',
            'customers',
            'drivers',
            'dailyMeals',
            'meals',
            'mealStats',
            'latestOrders',
            'customerTotal',
            'driverTotal',
            'planByPaymentType',
            'factByPaymentType',
            'planByMethod',
            'factByMethod'
        ));
    }


    public function index(Request $request)
    {
        $date = $request->input('order_date', now()->format('Y-m-d'));
//        $customers = Customer::with(['lastOrder.driver'])->get();

        $customers = Customer::with(['lastOrder.driver'])->get();
        $drivers = Driver::where('is_active', true)->get();

        // Bugungi orderlar
        $latestOrders = Order::with(['customer', 'meal1', 'meal2', 'meal3', 'meal4', 'driver'])
            ->where('order_date', $date)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        // Umumiy mijoz va haydovchi summalari
        $customerTotal = $latestOrders->sum('total_amount');
        $driverTotal = $latestOrders->sum('received_amount');

        // Plan - payment_type bo‘yicha
        $planByPaymentType = $latestOrders->groupBy('payment_type')->map(function ($group) {
            return $group->sum('total_amount');
        });

        // Fakt - payment_type bo‘yicha
        $factByPaymentType = $latestOrders->groupBy('payment_type')->map(function ($group) {
            return $group->sum('received_amount');
        });

        // ✅ Plan (payment_method bo‘yicha - customer_price)
        $planByMethod = $latestOrders->groupBy('payment_method')->map(function ($group) {
            return $group->sum('total_amount');
        });

        // ✅ Fakt (payment_method bo‘yicha - received_amount)
        $factByMethod = $latestOrders->groupBy('payment_method')->map(function ($group) {
            return $group->sum('received_amount');
        });

        // Bugungi DailyMeal'lar (itemlar bilan birga)
        $dailyMeals = DailyMeal::with('items')->where('date', $date)->get();

        // Barcha itemlar kolleksiyasi
        $meals = $dailyMeals->flatMap(function ($dailyMeal) {
            return $dailyMeal->items;
        })->groupBy('id')->map(function ($groupedItems) {
            $meal = $groupedItems->first();
            $totalCount = $groupedItems->sum(function ($item) {
                return $item->pivot->count ?? 0;
            });

            $meal->total_count = $totalCount;
            return $meal;
        })->values();

        // Har bir ovqat bo‘yicha statistikani tayyorlash
        $mealStats = [];

        foreach ($dailyMeals as $dailyMeal) {
            foreach ($dailyMeal->items as $item) {
                $orderedCount = $latestOrders->filter(function ($order) use ($item) {
                    return in_array($item->id, [
                        $order->meal_1_id,
                        $order->meal_2_id,
                        $order->meal_3_id,
                        $order->meal_4_id
                    ]);
                })->count();

                if (!isset($mealStats[$item->id])) {
                    $mealStats[$item->id] = [
                        'meal_name' => $item->name,
                        'initial_count' => $item->pivot->count,
                        'ordered_count' => $orderedCount,
                        'remaining' => $item->pivot->count - $orderedCount,
                    ];
                } else {
                    $mealStats[$item->id]['initial_count'] += $item->pivot->count;
                    $mealStats[$item->id]['ordered_count'] += $orderedCount;
                    $mealStats[$item->id]['remaining'] = $mealStats[$item->id]['initial_count'] - $mealStats[$item->id]['ordered_count'];
                }
            }
        }

        return view('admin.orders.index', compact(
            'date',
            'customers',
            'drivers',
            'dailyMeals',
            'meals',
            'mealStats',
            'latestOrders',
            'customerTotal',
            'driverTotal',
            'planByPaymentType',
            'factByPaymentType',
            'planByMethod',    // ✅ YANGI
            'factByMethod'     // ✅ YANGI
        ));
    }

    public function show(Request $request)
    {
        $date = $request->input('order_date', now()->format('Y-m-d'));

        $customers = Customer::all();
        $drivers = Driver::where('is_active', true)->get();

        // Bugungi orderlar
        $latestOrders = Order::with(['customer', 'meal1', 'meal2', 'meal3', 'meal4', 'driver'])
            ->where('order_date', $date)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        // Umumiy mijoz va haydovchi summalari
        $customerTotal = $latestOrders->sum('total_amount');
        $driverTotal = $latestOrders->sum('received_amount');

        // Plan - payment_type bo‘yicha
        $planByPaymentType = $latestOrders->groupBy('payment_type')->map(function ($group) {
            return $group->sum('total_amount');
        });

        // Fakt - payment_type bo‘yicha
        $factByPaymentType = $latestOrders->groupBy('payment_type')->map(function ($group) {
            return $group->sum('received_amount');
        });

        // ✅ Plan (payment_method bo‘yicha - customer_price)
        $planByMethod = $latestOrders->groupBy('payment_method')->map(function ($group) {
            return $group->sum('total_amount');
        });

        // ✅ Fakt (payment_method bo‘yicha - received_amount)
        $factByMethod = $latestOrders->groupBy('payment_method')->map(function ($group) {
            return $group->sum('received_amount');
        });

        // Bugungi DailyMeal'lar (itemlar bilan birga)
        $dailyMeals = DailyMeal::with('items')->where('date', $date)->get();

        // Barcha itemlar kolleksiyasi
        $meals = $dailyMeals->flatMap(function ($dailyMeal) {
            return $dailyMeal->items;
        })->groupBy('id')->map(function ($groupedItems) {
            $meal = $groupedItems->first();
            $totalCount = $groupedItems->sum(function ($item) {
                return $item->pivot->count ?? 0;
            });

            $meal->total_count = $totalCount;
            return $meal;
        })->values();

        // Har bir ovqat bo‘yicha statistikani tayyorlash
        $mealStats = [];

        foreach ($dailyMeals as $dailyMeal) {
            foreach ($dailyMeal->items as $item) {
                $orderedCount = $latestOrders->filter(function ($order) use ($item) {
                    return in_array($item->id, [
                        $order->meal_1_id,
                        $order->meal_2_id,
                        $order->meal_3_id,
                        $order->meal_4_id
                    ]);
                })->count();

                if (!isset($mealStats[$item->id])) {
                    $mealStats[$item->id] = [
                        'meal_name' => $item->name,
                        'initial_count' => $item->pivot->count,
                        'ordered_count' => $orderedCount,
                        'remaining' => $item->pivot->count - $orderedCount,
                    ];
                } else {
                    $mealStats[$item->id]['initial_count'] += $item->pivot->count;
                    $mealStats[$item->id]['ordered_count'] += $orderedCount;
                    $mealStats[$item->id]['remaining'] = $mealStats[$item->id]['initial_count'] - $mealStats[$item->id]['ordered_count'];
                }
            }
        }

        return view('admin.orders.show', compact(
            'date',
            'customers',
            'drivers',
            'dailyMeals',
            'meals',
            'mealStats',
            'latestOrders',
            'customerTotal',
            'driverTotal',
            'planByPaymentType',
            'factByPaymentType',
            'planByMethod',    // ✅ YANGI
            'factByMethod'     // ✅ YANGI
        ));
    }

    public function statistic(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $meals = Meal::all();

        $orders = Order::with('customer')
            ->whereDate('date', $date)
            ->get();

        $latestOrders = $orders;

        // To'lov turlarini guruhlash - PLAN uchun (total_amount)
        $planByPaymentType = $orders->groupBy('payment_type')->map(function ($group) {
            return $group->sum('total_amount');
        });

        // To'lov turlarini guruhlash - FAKT uchun (received_amount)
        $factByPaymentType = $orders->groupBy('payment_type')->map(function ($group) {
            return $group->sum('received_amount');
        });

        return view('admin.statistic.index', compact(
            'meals',
            'latestOrders',
            'planByPaymentType',
            'factByPaymentType',
            'date'
        ));
    }

    public function getMealsByDate(Request $request)
    {
        $date = $request->input('date');

        $dailyMeal = DailyMeal::with('items')->where('date', $date)->first();

        if (!$dailyMeal) {
            return response()->json(['meals' => []]);
        }

        return response()->json([
            'meals' => $dailyMeal->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $orders = $request->input('orders');
        $errors = new MessageBag();

        try {
            DB::beginTransaction();

            foreach ($orders as $i => $orderData) {
                if (!isset($orderData['customer_id']) || !is_numeric($orderData['customer_id'])) {
                    continue;
                }

                $orderDate = $orderData['order_date'] ?? now()->format('Y-m-d');
                $meals = $orderData['meals'] ?? [];
                $totalMealQty = array_sum(array_map('intval', $meals));

                if ($totalMealQty <= 0) {
                    $errors->add("orders.$i.meals", "Kamida bitta ovqat tanlang (qator: " . ($i + 1) . ").");
                    continue;
                }

                $customer = \App\Models\Customer::findOrFail($orderData['customer_id']);
                $dailyOrderCount = \App\Models\Order::whereDate('order_date', $orderDate)->count();
                $dailyOrderNumber = $dailyOrderCount + 1;

                // Balansni float qilib olish
                $cleanBalance = floatval(str_replace([' ', ','], ['', '.'], $customer->balance));

                // Ovqatlar ID va miqdorlari
                $mealIds = array_keys($meals);
                $mealQuantities = array_values($meals);

                $mealData = [
                    'meal_1_id' => $mealIds[0] ?? null,
                    'meal_1_quantity' => intval($mealQuantities[0] ?? 0),
                    'meal_2_id' => $mealIds[1] ?? null,
                    'meal_2_quantity' => intval($mealQuantities[1] ?? 0),
                    'meal_3_id' => $mealIds[2] ?? null,
                    'meal_3_quantity' => intval($mealQuantities[2] ?? 0),
                    'meal_4_id' => $mealIds[3] ?? null,
                    'meal_4_quantity' => intval($mealQuantities[3] ?? 0),
                ];

                // Cola
                $colaQty = intval($orderData['cola'] ?? 0);
                $colaPrice = 15000;
                $colaTotal = $colaQty * $colaPrice;

                // Ovqat summasi
                $mealTotal = 0;
                foreach ($meals as $mealId => $qty) {
                    $meal = \App\Models\Meal::find($mealId);
                    if ($meal) {
                        $mealTotal += $meal->price * intval($qty);
                    }
                }

                // Yetkazib berish
                $totalMealsQty = array_sum($mealQuantities);
                $deliveryFee = $totalMealsQty > 8
                    ? 0
                    : floatval(str_replace([' ', ','], ['', '.'], $orderData['delivery'] ?? 20000));

                $total = $mealTotal + $colaTotal + $deliveryFee;

                // BUYURTMA YARATISH
                $order = \App\Models\Order::create([
                    'customer_id' => $customer->id,
                    'meal_1_id' => $mealData['meal_1_id'],
                    'meal_1_quantity' => $mealData['meal_1_quantity'],
                    'meal_2_id' => $mealData['meal_2_id'],
                    'meal_2_quantity' => $mealData['meal_2_quantity'],
                    'meal_3_id' => $mealData['meal_3_id'],
                    'meal_3_quantity' => $mealData['meal_3_quantity'],
                    'meal_4_id' => $mealData['meal_4_id'],
                    'meal_4_quantity' => $mealData['meal_4_quantity'],
                    'cola_quantity' => $colaQty,
                    'delivery_fee' => $deliveryFee,
                    'driver_id' => $orderData['driver_id'] ?? null,
                    'order_date' => $orderDate,
                    'payment_method' => $orderData['payment_type'] ?? 'cash',
                    'total_meals' => $totalMealsQty,
                    'total_amount' => $total,
                    'daily_order_number' => $dailyOrderNumber,
                ]);

                // BALANSDAN AYIRISH — MINUS BO‘LSA HAM
                $customer->balance = $cleanBalance - $total;
                $customer->save();

                // Balans tarixiga yozish
                \App\Models\BalanceHistory::create([
                    'customer_id' => $customer->id,
                    'amount' => $total,
                    'type' => 'order',
                    'description' => "Buyurtma #{$order->id} uchun balansdan ayirildi (minus bo‘lishi mumkin).",
                ]);

                // DailyMeal stokdan ayirish
                $dailyMeals = \App\Models\DailyMeal::where('date', $orderDate)->get();
                foreach ($dailyMeals as $dailyMeal) {
                    foreach ($meals as $mealId => $qty) {
                        $item = $dailyMeal->items()->where('meal_id', $mealId)->first();
                        if ($item && $item->pivot) {
                            $currentCount = $item->pivot->count;
                            $newCount = max(0, $currentCount - intval($qty));
                            $dailyMeal->items()->updateExistingPivot($mealId, ['count' => $newCount]);
                        }
                    }
                }
            }

            if ($errors->isNotEmpty()) {
                DB::rollBack();
                return redirect()->back()->withErrors($errors)->withInput();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Buyurtmalar muvaffaqiyatli saqlandi!');
        } catch (\Exception $e) {
            DB::rollBack();

            logger()->error('Order save error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Xatolik: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $order = Order::with(['customer', 'driver', 'meal1','meal2','meal3','meal4'])->findOrFail($id);

        $date = $order->order_date;

        $customers = Customer::with(['lastOrder.driver'])->get();
        $drivers = Driver::where('is_active', true)->get();

        $dailyMeals = DailyMeal::with('items')->where('date', $date)->get();

        $meals = $dailyMeals->flatMap(function ($dailyMeal) {
            return $dailyMeal->items;
        })->values();

        // Ranglar arrayi
        $colors = ['#ff4d4f', '#1890ff', '#52c41a', '#faad14'];

        return view('admin.orders.edit', compact(
            'order',
            'date',
            'customers',
            'drivers',
            'dailyMeals',
            'meals',
            'colors'
        ));
    }

    public function update(Request $request, $id)
    {
        $errors = new MessageBag();

        try {
            DB::beginTransaction();

            $order = \App\Models\Order::findOrFail($id);
            $customer = \App\Models\Customer::findOrFail($order->customer_id);

            $orderDate = $request->input('order_date', $order->order_date);
            $meals = $request->input('meals', []);
            $totalMealQty = array_sum(array_map('intval', $meals));

            if ($totalMealQty <= 0) {
                $errors->add("meals", "Kamida bitta ovqat tanlang.");
            }

            if ($errors->isNotEmpty()) {
                DB::rollBack();
                return redirect()->back()->withErrors($errors)->withInput();
            }

            // Balansni float qilib olish
            $cleanBalance = floatval(str_replace([' ', ','], ['', '.'], $customer->balance));

            // Ovqat summasi
            $mealTotal = 0;
            foreach ($meals as $mealId => $qty) {
                $meal = \App\Models\Meal::find($mealId);
                if ($meal) {
                    $mealTotal += $meal->price * intval($qty);
                }
            }

            // Cola
            $colaQty = intval($request->input('cola', 0));
            $colaPrice = 15000;
            $colaTotal = $colaQty * $colaPrice;

            // Yetkazib berish narxi
            $totalMealsQty = array_sum(array_values($meals));
            $deliveryFee = $totalMealsQty > 8
                ? 0
                : floatval(str_replace([' ', ','], ['', '.'], $request->input('delivery', 20000)));

            $total = $mealTotal + $colaTotal + $deliveryFee;

            // BUYURTMA YANGILASH
            $order->update([
                'meal_1_id' => array_keys($meals)[0] ?? null,
                'meal_1_quantity' => intval(array_values($meals)[0] ?? 0),
                'meal_2_id' => array_keys($meals)[1] ?? null,
                'meal_2_quantity' => intval(array_values($meals)[1] ?? 0),
                'meal_3_id' => array_keys($meals)[2] ?? null,
                'meal_3_quantity' => intval(array_values($meals)[2] ?? 0),
                'meal_4_id' => array_keys($meals)[3] ?? null,
                'meal_4_quantity' => intval(array_values($meals)[3] ?? 0),
                'cola_quantity' => $colaQty,
                'delivery_fee' => $deliveryFee,
                'order_date' => $orderDate,
                'payment_method' => $request->input('payment_type', $order->payment_method),
                'total_meals' => $totalMealsQty,
                'total_amount' => $total,
            ]);

            // BALANSDAN AYIRISH / QO‘SHISH
            $balanceDiff = $total - $order->total_amount; // eski va yangi summaning farqi
            $customer->balance = $cleanBalance - $balanceDiff;
            $customer->save();

            // Balans tarixiga yozish
            \App\Models\BalanceHistory::create([
                'customer_id' => $customer->id,
                'amount' => abs($balanceDiff),
                'type' => 'order', // ENUM bo‘lsa faqat mavjud qiymat yoziladi
                'description' => "Buyurtma #{$order->id} yangilandi. Balansdan " . ($balanceDiff > 0 ? "ayirildi" : "qo‘shildi") . ".",
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Buyurtma muvaffaqiyatli yangilandi!');
        } catch (\Exception $e) {
            DB::rollBack();

            logger()->error('Order update error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return redirect()
                ->route('orders.index', request()->only(['status', 'date_from', 'date_to']))
                ->with('error', 'Xatolik: ' . $e->getMessage())
                ->withInput();
        }
    }


}
