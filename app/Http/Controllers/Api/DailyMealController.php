<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\DailyMeal;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Meal;
use App\Models\BalanceHistory;
use App\Models\Driver;

class DailyMealController extends Controller
{
    // Bugungi menyu
    public function index(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');

        $dailyMeals = DailyMeal::whereDate('date', $today)
            ->with('items')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($meal) {
                $meal->items = $meal->items->map(function ($item) {
                    $item->image = $item->image ? url('uploads/' . $item->image) : null;
                    return $item;
                });
                return $meal;
            })
            ->groupBy(fn($meal) => Carbon::parse($meal->date)->format('Y-m-d'));

        return response()->json([
            'date' => $today,
            'data' => $dailyMeals,
        ]);
    }

    // Barcha mijozlarni olish
    public function get()
    {
        $customers = Customer::all();
        return response()->json([
            'status' => 'success',
            'data' => $customers
        ]);
    }

    // Telegram ID bo‘yicha mijoz topish
    public function getByTelegram($telegram)
    {
        $customer = Customer::where('telegram', $telegram)->first();

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mijoz topilmadi'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $customer
        ]);
    }

    // Yangi mijoz qo‘shish
    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'nullable|exists:regions,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone',
            'telegram' => 'nullable|string|max:50|unique:customers,telegram',
            'status' => 'nullable|in:Active,Blok',
            'type' => 'nullable|in:oylik,odiy',
            'address' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:100',
            'location_coordinates' => 'nullable|string|max:255',
            'balance' => 'nullable|numeric',
            'balance_due_date' => 'nullable|date',
        ], [
            'phone.unique' => 'Bunday nomerdagi mijoz bor!',
            'telegram.unique' => 'Bu Telegram ID bilan mijoz mavjud!',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Mijoz muvaffaqiyatli qo‘shildi.',
            'data' => $customer
        ], 201);
    }

    // Buyurtma yaratish
    public function order(Request $request)
    {
        $orders = $request->input('orders');
        $errors = [];

        try {
            DB::beginTransaction();

            foreach ($orders as $i => $orderData) {
                if (empty($orderData['customer_id'])) continue;

                $orderDate = $orderData['order_date'] ?? now()->format('Y-m-d');
                $meals = $orderData['meals'] ?? [];
                $totalMealQty = array_sum(array_map('intval', $meals));

                if ($totalMealQty <= 0) {
                    $errors[] = ['row' => $i + 1, 'message' => "Kamida bitta ovqat tanlang."];
                    continue;
                }

                $customer = Customer::findOrFail($orderData['customer_id']);
                $dailyOrderNumber = Order::whereDate('order_date', $orderDate)->count() + 1;
                $cleanBalance = floatval($customer->balance);

                $mealIds = array_keys($meals);
                $mealQuantities = array_values($meals);

                $colaQty = intval($orderData['cola'] ?? 0);
                $colaPrice = 15000;

                // Ovqat summasi
                $mealTotal = 0;
                foreach ($meals as $mealId => $qty) {
                    $meal = Meal::find($mealId);
                    if ($meal) $mealTotal += $meal->price * intval($qty);
                }

                // Cola bepulmi?
                $colaTotal = $totalMealQty > 7 ? 0 : $colaQty * $colaPrice;

                // Yetkazib berish narxi
                $deliveryFee = $totalMealQty > 5
                    ? 0
                    : floatval($orderData['delivery'] ?? 20000);

                $total = $mealTotal + $colaTotal + $deliveryFee;

                // Order yaratish
                $order = Order::create([
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
                    'total_meals' => $totalMealQty,
                    'total_amount' => $total,
                    'daily_order_number' => $dailyOrderNumber,
                    'user_id' => auth()->id(),
                ]);

                // Balansni minus qilish
                $customer->balance = $cleanBalance - $total;
                $customer->save();

                BalanceHistory::create([
                    'customer_id' => $customer->id,
                    'amount' => $total,
                    'type' => 'order',
                    'description' => "Buyurtma #{$order->id} uchun balansdan ayirildi.",
                ]);

                // Telegramga xabar (soddalashtirilgan)
                $mealListText = '';
                foreach ($meals as $mealId => $qty) {
                    if ($qty > 0) {
                        $meal = Meal::find($mealId);
                        $mealListText .= "🍽 {$meal->name} — {$qty} dona\n";
                    }
                }
                if ($colaQty > 0) $mealListText .= "🥤 Cola — {$colaQty} dona\n";

                $telegramText = "📦 Buyurtma #{$order->daily_order_number}\n"
                    . "👤 {$customer->name}\n"
                    . "📞 {$customer->phone}\n"
                    . "💰 Umumiy: " . number_format($total, 0, '.', ' ') . " so‘m\n\n"
                    . $mealListText;

                $this->sendTelegramMessage($telegramText);
            }

            if (!empty($errors)) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'errors' => $errors], 422);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Buyurtmalar saqlandi!'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Xatolik: ' . $e->getMessage()
            ], 500);
        }
    }

    // Telegramga xabar yuborish
    private function sendTelegramMessage($text)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID'); // admin kanal yoki guruh id
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        try {
            \Http::post($url, [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            logger()->error("Telegramga xabar yuborilmadi: " . $e->getMessage());
        }
    }
}
