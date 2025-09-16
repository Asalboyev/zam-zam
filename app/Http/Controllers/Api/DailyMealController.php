<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\DailyMeal;

class DailyMealController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');

        $dailyMeals = DailyMeal::whereDate('date', $today)
            ->with('items')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($meal) {
                // Har bir meal ichidagi itemslarni o‘zgartiramiz
                $meal->items = $meal->items->map(function ($item) {
                    if ($item->img) {
                        $item->img_url = url('upload/images/' . $item->img);
                    } else {
                        $item->img_url = null;
                    }
                    return $item;
                });
                return $meal;
            })
            ->groupBy(function ($meal) {
                return Carbon::parse($meal->date)->format('Y-m-d');
            });

        return response()->json([
            'date' => $today,
            'data' => $dailyMeals,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'nullable|exists:regions,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone',
            'telegram' => 'nullable|string|max:50',
            'status' => 'nullable|in:Active,Blok',
            'type' => 'nullable|in:oylik,odiy',

            'address' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:100',
            'location_coordinates' => 'nullable|string|max:255',

            'balance' => 'nullable|numeric',
            'balance_due_date' => 'nullable|date',
        ], [
            'phone.unique' => 'Bunday nomerdagi mijoz bor!', // custom message
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Mijoz muvaffaqiyatli qo‘shildi.',
            'data' => $customer
        ], 201);
    }

    public function order(Request $request)
    {
        $orders = $request->input('orders');
        $errors = [];

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
                    $errors[] = [
                        'row' => $i + 1,
                        'message' => "Kamida bitta ovqat tanlang."
                    ];
                    continue;
                }

                $customer = \App\Models\Customer::findOrFail($orderData['customer_id']);
                $dailyOrderCount = \App\Models\Order::whereDate('order_date', $orderDate)->count();
                $dailyOrderNumber = $dailyOrderCount + 1;

                $cleanBalance = floatval(str_replace([' ', ','], ['', '.'], $customer->balance));

                $mealIds = array_keys($meals);
                $mealQuantities = array_values($meals);

                $colaQty = intval($orderData['cola'] ?? 0);
                $colaPrice = 15000;

                // Jami ovqatlar soni
                $totalMealsQty = array_sum($mealQuantities);

                // Ovqat summasi
                $mealTotal = 0;
                foreach ($meals as $mealId => $qty) {
                    $meal = \App\Models\Meal::find($mealId);
                    if ($meal) {
                        $mealTotal += $meal->price * intval($qty);
                    }
                }

                // Cola bepul sharti
                $colaTotal = $totalMealsQty > 7 ? 0 : $colaQty * $colaPrice;

                // Yetkazib berish
                $deliveryFee = $totalMealsQty > 5
                    ? 0
                    : floatval(str_replace([' ', ','], ['', '.'], $orderData['delivery'] ?? 20000));

                $total = $mealTotal + $colaTotal + $deliveryFee;

                // Order yaratish
                $order = \App\Models\Order::create([
                    'customer_id' => $customer->id,
                    'meal_1_id' => $mealIds[0] ?? null,
                    'meal_1_quantity' => intval($mealQuantities[0] ?? 0),
                    'meal_2_id' => $mealIds[1] ?? null,
                    'meal_2_quantity' => intval($mealQuantities[1] ?? 0),
                    'meal_3_id' => $mealIds[2] ?? null,
                    'meal_3_quantity' => intval($mealQuantities[2] ?? 0),
                    'meal_4_id' => $mealIds[3] ?? null,
                    'meal_4_quantity' => intval($mealQuantities[3] ?? 0),
                    'cola_quantity' => $colaQty,
                    'delivery_fee' => $deliveryFee,
                    'driver_id' => $orderData['driver_id'] ?? null,
                    'order_date' => $orderDate,
                    'payment_method' => $orderData['payment_type'] ?? 'cash',
                    'total_meals' => $totalMealsQty,
                    'total_amount' => $total,
                    'daily_order_number' => $dailyOrderNumber,
                    'user_id' => auth()->id(),
                ]);

                // Balansni yangilash
                $customer->balance = $cleanBalance - $total;
                $customer->save();

                \App\Models\BalanceHistory::create([
                    'customer_id' => $customer->id,
                    'amount' => $total,
                    'type' => 'order',
                    'description' => "Buyurtma #{$order->id} uchun balansdan ayirildi (minus bo‘lishi mumkin).",
                ]);

                // DailyMeal stok yangilash
                $dailyMeals = \App\Models\DailyMeal::where('date', $orderDate)->get();
                foreach ($dailyMeals as $dailyMeal) {
                    foreach ($meals as $mealId => $qty) {
                        $item = $dailyMeal->items()->where('meal_id', $mealId)->first();
                        if ($item && $item->pivot) {
                            $newCount = max(0, $item->pivot->count - intval($qty));
                            $newSell  = $item->pivot->sell + intval($qty);

                            $dailyMeal->items()->updateExistingPivot($mealId, [
                                'count' => $newCount,
                                'sell'  => $newSell,
                            ]);
                        }
                    }
                }

                // Telegram xabar
                $mealListText = '';
                foreach ($meals as $mealId => $qty) {
                    if ($qty > 0) {
                        $meal = \App\Models\Meal::find($mealId);
                        if ($meal) {
                            $mealListText .= "🍽 {$meal->name} — {$qty} dona\n";
                        }
                    }
                }
                if ($colaQty > 0) {
                    $mealListText .= "🥤 Cola — {$colaQty} dona\n";
                }

                $locationLink = '';
                if (!empty($customer->location_coordinates)) {
                    $url = "https://www.google.com/maps/search/?api=1&query=" . urlencode($customer->location_coordinates);
                    $locationLink = "📍 Location: [Map link]($url)";
                }

                $driverName = '';
                if (!empty($orderData['driver_id'])) {
                    $driver = \App\Models\Driver::find($orderData['driver_id']);
                    $driverName = $driver ? $driver->name : '';
                }

                $telegramText = "📦 Buyurtma  Telegram botdan #{$order->daily_order_number}\n" .
                    "👤 Mijoz: {$customer->name}\n" .
                    "📞 Tel: {$customer->phone}\n" .
                    ($driverName ? "🚚 Haydovchi: {$driverName}\n" : '') .
                    "📅 Sana: {$orderDate}\n\n" .
                    $mealListText .
                    "📦 Yetkazib berish: " . number_format($deliveryFee, 0, '.', ' ') . " so‘m\n" .
                    "💰 Umumiy: " . number_format($total, 0, '.', ' ') . " so‘m\n" .
                    ($locationLink ? "\n{$locationLink}" : '');

                $this->sendTelegramMessage($telegramText);
            }

            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'errors' => $errors
                ], 422);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Buyurtmalar muvaffaqiyatli saqlandi!'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            logger()->error('Order save error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Xatolik: ' . $e->getMessage()
            ], 500);
        }
    }



}
