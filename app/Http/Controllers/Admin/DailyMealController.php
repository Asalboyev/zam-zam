<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyMeal;
use Illuminate\Http\Request;
use App\Models\Meal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyMealController extends Controller
{


    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $startOfWeek = Carbon::parse($date)->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::parse($date)->endOfWeek(Carbon::SATURDAY);

        $dailyMeals = DailyMeal::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->with('items')
            ->orderBy('date', 'asc')
            ->get()
            ->groupBy(function ($meal) {
                return Carbon::parse($meal->date)->format('Y-m-d');
            });

        // 🔥 Bugungi kunni doim tepaga chiqarish
        $dailyMeals = $dailyMeals->sortBy(function ($meals, $day) {
            return Carbon::parse($day)->isToday() ? 0 : Carbon::parse($day)->timestamp;
        });

        return view('admin.daily_meal.index', compact('dailyMeals', 'date'));
    }


//    public function index(Request $request)
//    {
//        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
//
//        $startOfWeek = Carbon::parse($date)->startOfWeek(Carbon::MONDAY);
//        $endOfWeek = Carbon::parse($date)->endOfWeek(Carbon::SATURDAY);
//
//        $dailyMeals = DailyMeal::whereBetween('date', [$startOfWeek, $endOfWeek])
//            ->with('items')
//            ->orderBy('date', 'asc') // ✅ DESC emas ASC qilib qo‘yamiz
//            ->get()
//            ->groupBy(function ($meal) {
//                return Carbon::parse($meal->date)->format('Y-m-d');
//            });
//
//        return view('admin.daily_meal.index', compact('dailyMeals', 'date'));
//    }



    public function create()
    {
        $meals = Meal::where('is_active', true)->get();
        return view('admin.daily_meal.create', compact('meals'));
    }


    public function store(Request $request)
    {
        $dailyMeal = DailyMeal::create([
            'date' => $request->date,
        ]);

        DB::table('daily_meal_items')->insert([
            'daily_meal_id' => $dailyMeal->id,
            'meal_id' => $request->meal_id,
            'count' => $request->remaining_count,
            'remaining_count' => $request->remaining_count,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.daily_meal.index')->with('success', 'Saved');
    }

    public function edit($id)
    {
        // Faqat tekshirish uchun (keyinchalik olib tashlansa bo'ladi)
        // dd($id);

        // daily_meal_items jadvalidan tegishli elementni olish
        $item = DB::table('daily_meal_items')->where('id', $id)->first();

        // Agar topilmasa, 404 sahifani qaytarish
        if (!$item) {
            abort(404);
        }

        // Tanlangan meal_id (formdagi tanlangan variant uchun)
        $selected = [$item->meal_id];

        // Faol (is_active) bo'lgan meal larni olish
        $meals = Meal::where('is_active', true)->get();

        // create.blade.php sahifasidan foydalanilmoqda
        return view('admin.daily_meal.create', compact('meals', 'selected', 'item'));
    }
    public function itemUpdate(Request $request, $id)
    {
        $request->validate([
            'meal_id' => 'required|exists:meals,id',
            'count' => 'required|integer|nullable',
        ]);
//
//        DB::table('daily_meal_items')
//            ->where('id', $id)
//            ->update([
//                'meal_id' => $request->meal_id,
//                'count' => $request->count,
//                'remaining_count' => $request->count,
//
//                'updated_at' => now(),
//            ]);


//        DB::table('daily_meal_items')
//            ->where('id', $id)
//            ->update([
//                'meal_id' => $request->meal_id,
//                'count' => DB::raw('count + ' . (int) $request->count),
//                'remaining_count' => DB::raw('remaining_count + ' . (int) $request->count),
//                'updated_at' => now(),
//            ]);
        DB::table('daily_meal_items')
            ->where('id', $id)
            ->update([
                'meal_id' => $request->meal_id,
//                'sell' => $request->sell,
                'count' => (int) $request->count,
                'remaining_count' => (int) $request->remaining_count,
                'updated_at' => now(),
            ]);



        return redirect()->route('admin.daily_meal.index')->with('success', 'Ovqat muvaffaqiyatli yangilandi.');
    }
    public function destroy($id)
    {
        // daily_meal_items jadvalidan tegishli elementni topish
        $item = DB::table('daily_meal_items')->where('id', $id)->first();

        if (!$item) {
            abort(404); // agar topilmasa
        }

        // O‘chirish
        DB::table('daily_meal_items')->where('id', $id)->delete();

        return redirect()->route('admin.daily_meal.index')->with('success', 'Item deleted');
    }



}
