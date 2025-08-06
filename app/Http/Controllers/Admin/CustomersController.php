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

class CustomersController extends Controller
{
    public function index(){
        $customers = Customer::latest()
            ->paginate(12);
        return view('admin.customers.index',compact('customers'));
    }
    public function create(){
        return view('admin.customers.create');
    }


    public function dashboard()
    {
        $mealCount = Meal::count();
        $customerCount = Customer::count();
        $driverCount = Driver::count();

        $today = Carbon::today();
        $dailySales = Order::whereDate('order_date', $today)->sum('total_amount');

        // Oxirgi 7 kunlik buyurtmalar statistikasi (grafik uchun)
        $last7Days = collect();
        $labels = collect();
        $today = Carbon::today();

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $labels->push($date->format('M d'));
            $sales = Order::whereDate('order_date', $date)->sum('total_amount');
            $last7Days->push($sales);
        }

        return view('admin.dashboard', compact(
            'mealCount',
            'customerCount',
            'driverCount',
            'dailySales',
            'labels',
            'last7Days'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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

    public function edit(Customer $customer){
        return view('admin.customers.edit',compact('customer'));
    }
    public function update(Request $request, Customer $customer)
    {


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'telegram' => 'nullable|string|max:50',
            'status' => 'required|in:Active,Blok',
            'type' => 'required|in:Oylik mijoz,Odiy',

            'address' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:100',
            'location_coordinates' => 'nullable|string|max:255',

            'balance' => 'nullable|numeric',
            'balance_due_date' => 'nullable|date',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Mijoz maʼlumotlari yangilandi.');
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
