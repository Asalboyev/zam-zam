<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Meal;
use App\Models\Order;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BalanceHistory;
use App\Models\DailyMeal;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Customer::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('telegram', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%")
                    ->orWhereHas('region', function ($r) use ($search) {
                        $r->where('name', 'like', "%{$search}%");
                    });
            });
        })
            ->latest()
            ->paginate(12);

        return view('admin.customers.index', compact('customers', 'search'));
    }


    public function indebted_customers()
    {
        // faqat balance < 0 bo'lgan mijozlarni oling
        $customers = Customer::where('balance', '<', 0)
            ->latest()
            ->paginate(12);

        return view('admin.customers.indebted_customers', compact('customers'));
    }

    public function create()
    {
        $regions = \DB::table('regions')->orderBy('name')->get(); // viloyatlar ro‘yxati
        return view('admin.customers.create', compact('regions'));
    }

    // App\Http\Controllers\CustomerController.php

    public function Histories($id)
    {
        $customer = Customer::with('balanceHistories')->findOrFail($id);

        return view('admin.customers.histories', compact('customer'));
    }


    public function dashboard(Request $request)
    {
        $mealCount     = Meal::count();
        $customerCount      = Customer::count();
        $monthlyCustomer    = Customer::where('type', 'oylik')->count();
        $ordinaryCustomer   = Customer::where('type', 'odiy')->count();

        $orderCount = Order::count();
        $monthlyAverage = Order::selectRaw('COUNT(*) / COUNT(DISTINCT DATE_FORMAT(order_date, "%Y-%m")) as avg_per_month')
            ->value('avg_per_month');
        $monthlyAverage = round($monthlyAverage, 1); // yaxlitlab olish

        // Mijozlar balanslari
        $monthlyBalance = Customer::where('balance', '>', 0)->sum('balance');
        $monthlyDebt = Customer::where('balance', '<', 0)->sum('balance');

        // Qarzni musbat ko‘rsatish uchun abs olamiz
        $monthlyDebt = abs($monthlyDebt);

        // Qarzdor mijozlar soni (balansi manfiy bo‘lganlar)
        $debtorCount = Customer::where('balance', '<', 0)->count();

// To‘lanmagan buyurtmalar soni (received_amount = 0)
        $unpaidOrdersCount = Order::where('received_amount', 0)->count();


        $today         = Carbon::today();
        $dailySales = Order::whereDate('order_date', $today)->sum('total_amount');

        /*
         |========================
         |   DAILY SALES (DAROMAD)
         |========================
        */
        $dailyLabels    = collect();
        $dailySalesData = collect();
        $dailyDate      = $request->input('daily_date');

        if ($dailyDate) {
            // Oyni aniqlash
            $startOfMonth = Carbon::parse($dailyDate)->startOfMonth();
            $endOfMonth   = Carbon::parse($dailyDate)->endOfMonth();

            // Har bir kunni olish
            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $sales = Order::whereDate('order_date', $date)->sum('total_amount');
                $dailyLabels->push($date->format('d M')); // Masalan, "01 Aug"
                $dailySalesData->push($sales);
            }
        } else {
            // Agar oy tanlanmagan bo‘lsa, joriy oyning kunlarini chiqarish
            $today = Carbon::today();
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth   = $today->copy()->endOfMonth();

            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $sales = Order::whereDate('order_date', $date)->sum('total_amount');
                $dailyLabels->push($date->format('d M'));
                $dailySalesData->push($sales);
            }
        }


        /*
         |========================
         |   MONTHLY SALES (DAROMAD)
         |========================
        */
        $monthlyLabels    = collect();
        $monthlySalesData = collect();
        $startMonth       = $request->input('start_month');
        $endMonth         = $request->input('end_month');

        $start = $startMonth ? Carbon::parse($startMonth)->startOfMonth() : Carbon::now()->startOfYear();
        $end   = $endMonth ? Carbon::parse($endMonth)->endOfMonth() : Carbon::now();

        $period = $start->monthsUntil($end->copy()->addMonth());
        foreach ($period as $month) {
            $sales = Order::whereYear('order_date', $month->year)
                ->whereMonth('order_date', $month->month)
                ->sum('total_amount');
            $monthlyLabels->push($month->format('F Y'));
            $monthlySalesData->push($sales);
        }

        /*
         |========================
         |   DAILY ORDERS (BUYURTMALAR)
         |========================
        */
        $dailyOrdersLabels = collect();
        $dailyOrdersData   = collect();
        $dailyOrdersDate   = $request->input('daily_orders_date');

        if ($dailyOrdersDate) {
            // Tanlangan oyning birinchi va oxirgi kunlarini olish
            $startOfMonth = Carbon::parse($dailyOrdersDate)->startOfMonth();
            $endOfMonth   = Carbon::parse($dailyOrdersDate)->endOfMonth();

            // Har bir kunni aylantirib chiqamiz
            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $count = Order::whereDate('order_date', $date)->count();
                $dailyOrdersLabels->push($date->format('d M')); // Masalan: 01 Aug
                $dailyOrdersData->push($count);
            }
        } else {
            // Agar sana tanlanmagan bo‘lsa, joriy oyning kunlarini chiqarish
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth   = $today->copy()->endOfMonth();

            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $count = Order::whereDate('order_date', $date)->count();
                $dailyOrdersLabels->push($date->format('d M'));
                $dailyOrdersData->push($count);
            }
        }

        /*
         |========================
         |   MONTHLY ORDERS (BUYURTMALAR)
         |========================
        */
        $monthlyOrdersLabels = collect();
        $monthlyOrdersData   = collect();
        $ordersStartMonth    = $request->input('orders_start_month');
        $ordersEndMonth      = $request->input('orders_end_month');

        $start = $ordersStartMonth ? Carbon::parse($ordersStartMonth)->startOfMonth() : Carbon::now()->startOfYear();
        $end   = $ordersEndMonth ? Carbon::parse($ordersEndMonth)->endOfMonth() : Carbon::now();

        $period = $start->monthsUntil($end->copy()->addMonth());
        foreach ($period as $month) {
            $count = Order::whereYear('order_date', $month->year)
                ->whereMonth('order_date', $month->month)
                ->count();
            $monthlyOrdersLabels->push($month->format('F Y'));
            $monthlyOrdersData->push($count);
        }

        /*
         |========================
         |   DAILY MEALS (OVQAT QOLDIQ)
         |========================
        */
            $dailyMealsLabels = collect();
            $dailyMealsData   = collect();
            $dailyMealsDate   = $request->input('daily_meals_date');

        if ($dailyMealsDate) {
            // Tanlangan oyning birinchi va oxirgi sanasini olish
            $startOfMonth = Carbon::parse($dailyMealsDate)->startOfMonth();
            $endOfMonth   = Carbon::parse($dailyMealsDate)->endOfMonth();

            // Har bir kun bo'yicha hisoblash
            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $count = DailyMeal::with('items')
                    ->whereDate('date', $date)
                    ->get()
                    ->sum(fn($meal) => $meal->items->sum('pivot.remaining_count'));

                $dailyMealsLabels->push($date->format('d M')); // Masalan: 01 Aug
                $dailyMealsData->push($count);
            }
        } else {
            // Agar sana tanlanmagan bo'lsa, joriy oyning kunlarini chiqarish
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth   = $today->copy()->endOfMonth();

            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $count = DailyMeal::with('items')
                    ->whereDate('date', $date)
                    ->get()
                    ->sum(fn($meal) => $meal->items->sum('pivot.remaining_count'));

                $dailyMealsLabels->push($date->format('d M'));
                $dailyMealsData->push($count);
            }
        }

        /*
         |========================
         |   MONTHLY MEALS (OVQAT QOLDIQ)
         |========================
        */
        $monthlyMealsLabels = collect();
        $monthlyMealsData   = collect();
        $mealsStartMonth    = $request->input('meals_start_month');
        $mealsEndMonth      = $request->input('meals_end_month');

        $start = $mealsStartMonth ? Carbon::parse($mealsStartMonth)->startOfMonth() : Carbon::now()->startOfYear();
        $end   = $mealsEndMonth ? Carbon::parse($mealsEndMonth)->endOfMonth() : Carbon::now();

        $period = $start->monthsUntil($end->copy()->addMonth());
        foreach ($period as $month) {
            $count = DailyMeal::with('items')
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->get()
                ->sum(fn($meal) => $meal->items->sum('pivot.remaining_count'));

            $monthlyMealsLabels->push($month->format('F Y'));
            $monthlyMealsData->push($count);
        }
        /*
 |========================
 |   DAILY MEALS (Kunlik)
 |========================
*/
        $dailyOlindiData  = collect(); // ko‘k
        $dailySotildiData = collect(); // yashil
        $dailyQoldiData   = collect(); // qizil

        $dailyMealsDate = $request->input('daily_meals_order_date');

        if ($dailyMealsDate) {
            // Tanlangan oyning birinchi va oxirgi sanalarini olish
            $startOfMonth = Carbon::parse($dailyMealsDate)->startOfMonth();
            $endOfMonth   = Carbon::parse($dailyMealsDate)->endOfMonth();

            // Shu oyning barcha kunlarini aylantirish
            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $meals = DailyMeal::with('items')->whereDate('date', $date)->get();

                $dailyOlindiData->push($meals->sum(fn($meal) => $meal->items->sum('pivot.remaining_count')));
                $dailySotildiData->push($meals->sum(fn($meal) => $meal->items->sum('pivot.sell')));
                $dailyQoldiData->push($meals->sum(fn($meal) => $meal->items->sum('pivot.count')));
            }
        } else {
            // Agar sana tanlanmagan bo'lsa, joriy oyning barcha kunlarini chiqarish
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth   = $today->copy()->endOfMonth();

            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $meals = DailyMeal::with('items')->whereDate('date', $date)->get();

                $dailyOlindiData->push($meals->sum(fn($meal) => $meal->items->sum('pivot.remaining_count')));
                $dailySotildiData->push($meals->sum(fn($meal) => $meal->items->sum('pivot.sell')));
                $dailyQoldiData->push($meals->sum(fn($meal) => $meal->items->sum('pivot.count')));
            }
        }

        /*
         |========================
         |   MONTHLY MEALS (Oylik)
         |========================
        */
        $monthlyOlindiData  = collect(); // ko‘k
        $monthlySotildiData = collect(); // yashil
        $monthlyQoldiData   = collect(); // qizil

        $mealsStartMonth = $request->input('meals_order_start_month');
        $mealsEndMonth   = $request->input('meals_order_end_month');

        $start = $mealsStartMonth ? Carbon::parse($mealsStartMonth)->startOfMonth() : Carbon::now()->startOfYear();
        $end   = $mealsEndMonth ? Carbon::parse($mealsEndMonth)->endOfMonth() : Carbon::now();

        $period = $start->monthsUntil($end->copy()->addMonth());
        foreach ($period as $month) {
            $meals = DailyMeal::with('items')
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->get();

            $monthlyOlindiData->push($meals->sum(fn($meal) => $meal->items->sum('pivot.remaining_count')));
            $monthlySotildiData->push($meals->sum(fn($meal) => $meal->items->sum('pivot.sell')));
            $monthlyQoldiData->push($meals->sum(fn($meal) => $meal->items->sum('pivot.count')));
        }
        /*
 |========================
 |   DAILY CLIENTS (Kunlik mijozlar)
 |========================
*/
        $dailyClientsData = collect();
        $dailyClientsLabels = collect();

        $dailyClientsDate = $request->input('daily_clients_date');

        if ($dailyClientsDate) {
            // Tanlangan oyning birinchi va oxirgi sanalarini olish
            $startOfMonth = Carbon::parse($dailyClientsDate)->startOfMonth();
            $endOfMonth   = Carbon::parse($dailyClientsDate)->endOfMonth();

            // Shu oyning barcha kunlari bo'yicha ma'lumotlarni olish
            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $count = Customer::whereDate('created_at', $date->toDateString())->count();

                $dailyClientsData->push($count);
                $dailyClientsLabels->push($date->format('d-M')); // Masalan: 01-Aug
            }
        } else {
            // Agar sana tanlanmagan bo'lsa, joriy oyning barcha kunlarini chiqarish
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth   = $today->copy()->endOfMonth();

            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $count = Customer::whereDate('created_at', $date->toDateString())->count();

                $dailyClientsData->push($count);
                $dailyClientsLabels->push($date->format('d-M'));
            }
        }

        /*
         |========================
         |   MONTHLY CLIENTS (Oylik mijozlar)
         |========================
        */
        $monthlyClientsData = collect();
        $monthlyClientsLabels = collect();

        $clientsStartMonth = $request->input('clients_start_month');
        $clientsEndMonth   = $request->input('clients_end_month');

        $start = $clientsStartMonth ? Carbon::parse($clientsStartMonth)->startOfMonth() : Carbon::now()->startOfYear();
        $end   = $clientsEndMonth ? Carbon::parse($clientsEndMonth)->endOfMonth() : Carbon::now();

        $period = $start->monthsUntil($end->copy()->addMonth());
        foreach ($period as $month) {
            $monthlyClientsData->push(
                Customer::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count()
            );
            $monthlyClientsLabels->push($month->format('M-Y'));
        }



        return view('admin.dashboard', compact(
            'mealCount',

            'customerCount',
            'monthlyCustomer',
            'ordinaryCustomer',

            'orderCount',
            'monthlyAverage',

            'monthlyBalance',
            'monthlyDebt',

            'debtorCount',
            'unpaidOrdersCount',

            'dailySales',

            'dailyLabels',
            'dailySalesData',
            'monthlyLabels',
            'monthlySalesData',

            'dailyOrdersLabels',
            'dailyOrdersData',
            'monthlyOrdersLabels',
            'monthlyOrdersData',

            'dailyMealsLabels',
            'dailyMealsData',
            'monthlyMealsLabels',
            'monthlyMealsData',

            'dailyOlindiData',
            'dailySotildiData',
            'dailyQoldiData' ,

            'monthlyOlindiData',
            'monthlySotildiData',
            'monthlyQoldiData',

            'dailyClientsData',
            'dailyClientsLabels',

            'monthlyClientsData',
            'monthlyClientsLabels',
        ));
    }




    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'telegram' => 'nullable|string|max:50',
            'status' => 'required|in:Active,Blok',
            'type' => 'required|in:oylik,odiy',


            'address' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:100',
            'location_coordinates' => 'nullable|string|max:255',

            'balance' => 'nullable|numeric',
            'balance_due_date' => 'nullable|date',
        ]);

        $customer = Customer::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Mijoz muvaffaqiyatli qo‘shildi.');
    }

    public function edit(Customer $customer)
    {
        $regions = \DB::table('regions')->orderBy('name')->get(); // viloyatlar ro‘yxati

        return view('admin.customers.edit', compact('customer', 'regions'));
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            $validated = $request->validate([
                'region_id' => 'required|exists:regions,id',
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'telegram' => 'nullable|string|max:50',
                'status' => 'required|in:Active,Blok',
                'type' => 'required|in:oylik,odiy',
                'address' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:100',
                'location_coordinates' => 'nullable|string|max:255',
                'balance' => 'nullable|numeric',
                'balance_due_date' => 'nullable|date',
            ]);

            $additionalBalance = $validated['balance'] ?? 0;
            $oldBalance = $customer->balance;
            $newBalance = $oldBalance + $additionalBalance;

            $updateData = $validated;
            $updateData['balance'] = $newBalance;

            Log::info('Customer update data:', $updateData);

            $customer->update($updateData);

            if ($additionalBalance > 0) {
                BalanceHistory::create([
                    'customer_id' => $customer->id,
                    'amount' => $additionalBalance,
                    'type' => 'payment',
                    'description' => 'Admin tomonidan balans to‘ldirildi',
                ]);
            }

            return redirect()->route('admin.customers.index')
                ->with('success', 'Mijoz maʼlumotlari yangilandi.');
        } catch (\Throwable $e) {
            Log::error('Customer update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Xatolik yuz berdi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        $meals = Meal::all();

        $latestOrders = Order::where('customer_id', $customer->id)
            ->with(['customer', 'driver']) // eager load qilish tavsiya etiladi
            ->latest()
            ->take(10)
            ->get();

        return view('admin.customers.show', compact('customer', 'latestOrders', 'meals'));
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Mijoz muvaffaqiyatli o‘chirildi.');
    }


}
