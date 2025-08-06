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
class OrdersController extends Controller
{

//    public function index(Request $request)
//    {
//        $date = $request->input('order_date', now()->format('Y-m-d'));
//
//        $customers = Customer::all();
//        $drivers = Driver::where('is_active', true)->get();
//
//        // Bugungi orderlar
//        $latestOrders = Order::with(['customer', 'meal1', 'meal2', 'meal3', 'meal4', 'driver'])
//            ->where('order_date', $date)
//            ->orderBy('created_at', 'desc')
//            ->take(50)
//            ->get();
//
//        // Umumiy mijoz va haydovchi summalari
//        $customerTotal = $latestOrders->sum('customer_price');
//        $driverTotal = $latestOrders->sum('driver_price');
//
//        // Plan - to'lov turiga qarab grouping (total_amount)
//        $planByPaymentType = $latestOrders->groupBy('payment_type')->map(function ($group) {
//            return $group->sum('total_amount');
//        });
//
//        // Fakt - to'lov turiga qarab grouping (received_amount)
//        $factByPaymentType = $latestOrders->groupBy('payment_type')->map(function ($group) {
//            return $group->sum('received_amount');
//        });
//
//        // Bugungi DailyMeal'lar (itemlar bilan birga)
//        $dailyMeals = DailyMeal::with('items')->where('date', $date)->get();
//
//        // Barcha itemlar kolleksiyasi
//        $meals = $dailyMeals->flatMap(function ($dailyMeal) {
//            return $dailyMeal->items;
//        })->groupBy('id')->map(function ($groupedItems) {
//            $meal = $groupedItems->first(); // asosiy ovqat
//            $totalCount = $groupedItems->sum(function ($item) {
//                return $item->pivot->count ?? 0;
//            });
//
//            $meal->total_count = $totalCount;
//            return $meal;
//        })->values();
//
//        // Har bir ovqat bo‘yicha statistikani tayyorlash
//        $mealStats = [];
//
//        foreach ($dailyMeals as $dailyMeal) {
//            foreach ($dailyMeal->items as $item) {
//                $orderedCount = $latestOrders->filter(function ($order) use ($item) {
//                    return in_array($item->id, [
//                        $order->meal_1_id,
//                        $order->meal_2_id,
//                        $order->meal_3_id,
//                        $order->meal_4_id
//                    ]);
//                })->count();
//
//                if (!isset($mealStats[$item->id])) {
//                    $mealStats[$item->id] = [
//                        'meal_name' => $item->name,
//                        'initial_count' => $item->pivot->count,
//                        'ordered_count' => $orderedCount,
//                        'remaining' => $item->pivot->count - $orderedCount,
//                    ];
//                } else {
//                    $mealStats[$item->id]['initial_count'] += $item->pivot->count;
//                    $mealStats[$item->id]['ordered_count'] += $orderedCount;
//                    $mealStats[$item->id]['remaining'] = $mealStats[$item->id]['initial_count'] - $mealStats[$item->id]['ordered_count'];
//                }
//            }
//        }
//
//        return view('admin.orders.index', compact(
//            'date',
//            'customers',
//            'drivers',
//            'dailyMeals',
//            'meals',
//            'mealStats',
//            'latestOrders',
//            'customerTotal',
//            'driverTotal',
//            'planByPaymentType',
//            'factByPaymentType'
//        ));
//    }

    public function index(Request $request)
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
        $orderDate = $request->input('order_date') ?? yesterday()->format('Y-m-d');

        try {
            DB::beginTransaction();

            foreach ($orders as $orderData) {
                // Mijoz tanlanmagan bo‘lsa, o'tkazib yuboriladi
                if (!isset($orderData['customer_id']) || !is_numeric($orderData['customer_id'])) {
                    continue;
                }

                $customer = \App\Models\Customer::findOrFail($orderData['customer_id']);
                $dailyOrderNumber = \App\Models\Order::where('customer_id', $customer->id)
                        ->whereDate('order_date', $orderDate)
                        ->count() + 1;

                // Balansni floatga aylantirish
                $cleanBalance = floatval(str_replace([' ', ','], ['', '.'], $customer->balance));

                // Ovqatlar
                $meals = $orderData['meals'] ?? [];
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

                $colaQty = intval($orderData['cola'] ?? 0);
                $colaPrice = 15000;
                $colaTotal = $colaQty * $colaPrice;

                $mealTotal = 0;
                foreach ($meals as $mealId => $qty) {
                    $meal = \App\Models\Meal::find($mealId);
                    if ($meal) {
                        $mealTotal += $meal->price * intval($qty);
                    }
                }

                $totalMealsQty = array_sum($mealQuantities);
                $deliveryFee = $totalMealsQty > 8
                    ? 0
                    : floatval(str_replace([' ', ','], ['', '.'], $orderData['delivery'] ?? 20000));

                $total = $mealTotal + $colaTotal + $deliveryFee;

                // Shart: balans yetarlimi yoki oylik mijoz
                $isOylikCustomer = strtolower($customer->type) == 'oylik';

                if ($cleanBalance >= $total || $isOylikCustomer) {
                    // Order saqlanmoqda
                    $order = \App\Models\Order::create([
                        'customer_id' => $customer->id,
                        'order_date' => $orderDate,

                        // Ovqatlar
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
                        'payment_method' => $orderData['payment_type'] ?? 'cash',
                        'total_meals' => $totalMealsQty,
                        'total_amount' => $total,
                        'daily_order_number' => $dailyOrderNumber,
                    ]);

                    // Balansdan ayirish
                    $customer->balance = $cleanBalance - $total;
                    $customer->save();

                    \App\Models\BalanceHistory::create([
                        'customer_id' => $customer->id,
                        'amount' => $total,
                        'type' => 'order',
                        'description' => "Buyurtma #{$order->id} uchun balansdan yechildi.",
                    ]);

                    // ⬇️⬇️⬇️  SHU YERDA `daily_meal_items` dan count kamaytiramiz

                    $dailyMeal = \App\Models\DailyMeal::where('date', $orderDate)->first();

                    if ($dailyMeal) {
                        foreach ($meals as $mealId => $qty) {
                            $item = $dailyMeal->items()->where('meal_id', $mealId)->first();

                            if ($item && $item->pivot) {
                                $currentCount = $item->pivot->count;
                                $newCount = max(0, $currentCount - intval($qty));

                                // Pivot jadvalni yangilash
                                $dailyMeal->items()->updateExistingPivot($mealId, ['count' => $newCount]);
                            }
                        }
                    }

                    // ⬆️⬆️⬆️ COUNT kamaytirish tugadi
                } else {
                    continue;
                }
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

            return redirect()->back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    public function edit(Request $request, $id)
    {
        $date = $request->input('order_date', now()->format('Y-m-d'));

        $customers = Customer::all();
        $drivers = Driver::where('is_active', true)->get();

        // ✅ 1) Tanlangan orderni yuklaymiz (meallar va customer bilan birga)
        $order = Order::with(['customer', 'driver', 'meal1', 'meal2', 'meal3', 'meal4'])->findOrFail($id);

        // ✅ 2) Bugungi barcha orderlar statistikasi uchun
        $latestOrders = Order::with(['customer', 'meal1', 'meal2', 'meal3', 'meal4', 'driver'])
            ->where('order_date', $date)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        // Statistika
        $customerTotal = $latestOrders->sum('total_amount');
        $driverTotal = $latestOrders->sum('received_amount');

        $planByPaymentType = $latestOrders->groupBy('payment_type')->map(fn($g) => $g->sum('total_amount'));
        $factByPaymentType = $latestOrders->groupBy('payment_type')->map(fn($g) => $g->sum('received_amount'));

        $planByMethod = $latestOrders->groupBy('payment_method')->map(fn($g) => $g->sum('total_amount'));
        $factByMethod = $latestOrders->groupBy('payment_method')->map(fn($g) => $g->sum('received_amount'));

        // DailyMeal va meals
        $dailyMeals = DailyMeal::with('items')->where('date', $date)->get();

        $meals = $dailyMeals->flatMap(fn($dm) => $dm->items)
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

        // ✅ Tanlangan ovqatlar va quantity'lar: [meal_id => quantity]
        $selectedMeals = [];

        for ($i = 1; $i <= 4; $i++) {
            $mealId = $order->{'meal_' . $i . '_id'};
            $quantity = $order->{'meal_' . $i . '_quantity'};

            if ($mealId && $quantity) {
                $selectedMeals[$mealId] = $quantity;
            }
        }

        return view('admin.orders.edit', compact(
            'order',
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
            'factByMethod',
            'selectedMeals' // ✅ view ga yuborildi
        ));
    }
    public function update(Request $request, $id)
    {

//        dd($request->all());
        $orderData = $request->input('orders')[0] ?? null;
        $orderDate = $request->input('order_date') ?? now()->format('Y-m-d');

        if (!$orderData || !isset($orderData['customer_id'])) {
            return redirect()->back()->with('error', 'Mijoz tanlanmagan.');
        }

        try {
            DB::beginTransaction();

            $order = \App\Models\Order::findOrFail($id);
            $customer = \App\Models\Customer::findOrFail($orderData['customer_id']);

            $mealIds = array_keys($orderData['meals'] ?? []);
            $mealQuantities = array_values($orderData['meals'] ?? []);

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

            $colaQty = intval($orderData['cola'] ?? 0);
            $colaPrice = 15000;
            $colaTotal = $colaQty * $colaPrice;

            $mealTotal = 0;
            foreach ($orderData['meals'] as $mealId => $qty) {
                $meal = \App\Models\Meal::find($mealId);
                if ($meal) {
                    $mealTotal += $meal->price * intval($qty);
                }
            }

            $totalMealsQty = array_sum($mealQuantities);
            $deliveryFee = $totalMealsQty > 8
                ? 0
                : floatval(str_replace([' ', ','], ['', '.'], $orderData['delivery'] ?? 20000));

            $total = $mealTotal + $colaTotal + $deliveryFee;

            // Avvalgi narxni balansga qaytarib qo‘yamiz
            $oldTotal = $order->total_amount;
            $customer->balance += $oldTotal;

            // Yangi balans yetadimi?
            $cleanBalance = floatval(str_replace([' ', ','], ['', '.'], $customer->balance));
            $received_amount = floatval(str_replace([' ', ','], ['', '.'], $request->received_amount ?? 0));

            $isOylik = strtolower($customer->type) === 'oylik';

            if (!$isOylik && $cleanBalance < $total) {
                return redirect()->back()->with('error', 'Balans yetarli emas.');
            }

            // buyurtma yangilanmoqda
            $order->update([
                'customer_id' => $customer->id,
                'order_date' => $orderDate,
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
                'payment_method' => $orderData['payment_type'] ?? 'cash',
                'total_meals' => $totalMealsQty,
                'total_amount' => $total,
                'received_amount' => $received_amount, // ✅ endi bu qiymat mavjud bo‘ladi

            ]);

            // balansdan yangilangan qiymatni olib tashlash
            $customer->balance = $cleanBalance - $total;
            $customer->save();

            // balans tarixi
            \App\Models\BalanceHistory::create([
                'customer_id' => $customer->id,
                'amount' => $total,
                'type' => 'order',
                'description' => "Buyurtma #{$order->id} yangilandi. Yangi summa: {$total}",
            ]);

            // DailyMeal count yangilash
            $dailyMeal = \App\Models\DailyMeal::where('date', $orderDate)->first();
            if ($dailyMeal) {
                foreach ($orderData['meals'] as $mealId => $qty) {
                    $item = $dailyMeal->items()->where('meal_id', $mealId)->first();
                    if ($item && $item->pivot) {
                        $currentCount = $item->pivot->count;
                        $newCount = max(0, $currentCount - intval($qty));
                        $dailyMeal->items()->updateExistingPivot($mealId, ['count' => $newCount]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.orders.index')->with('success', 'Buyurtma yangilandi.');

        } catch (\Exception $e) {
            DB::rollBack();

            logger()->error('Order update error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }



}
