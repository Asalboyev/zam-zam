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
        $date = $request->input('date');

        if ($date) {
            $dailyMeals = DailyMeal::with('items')->whereDate('date', $date)->get();
        } else {
            $today = Carbon::today()->toDateString();
            $dailyMeals = DailyMeal::with('items')->whereDate('date', $today)->get();

            if ($dailyMeals->isEmpty()) {
                $yesterday = Carbon::yesterday()->toDateString();
                $dailyMeals = DailyMeal::with('items')->whereDate('date', $yesterday)->get();
                $date = $yesterday;
            } else {
                $date = $today;
            }
        }

        return view('admin.daily_meal.index', compact('dailyMeals', 'date'));
    }



    public function create()
    {
        $meals = Meal::where('is_active', true)->get();
        return view('admin.daily_meal.create', compact('meals'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $count = DailyMeal::where('date', $value)->count();
                    if ($count >= 4) {
                        $fail("{$value} sanasiga allaqachon 4 ta ovqat qo‘shilgan.");
                    }
                }
            ],
            'meal_id' => 'required|exists:meals,id', // faqat bitta id keladi
        ]);

        // Sana bo‘yicha mavjud DailyMeal topamiz yoki yaratamiz
        $dailyMeal = DailyMeal::firstOrCreate([
            'date' => $validated['date'],
        ]);

        // Yangi ovqatni mavjud ovqatlarga qo‘shamiz
        if (!$dailyMeal->items->contains($validated['meal_id'])) {
            $dailyMeal->items()->attach($validated['meal_id']);
        }

        return redirect()->route('admin.daily_meal.index')
            ->with('success', 'Ovqat muvaffaqiyatli qo‘shildi.');
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
            'count' => 'required|integer|min:1',
        ]);

        DB::table('daily_meal_items')
            ->where('id', $id)
            ->update([
                'meal_id' => $request->meal_id,
                'count' => $request->count,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.daily_meal.index')->with('success', 'Ovqat muvaffaqiyatli yangilandi.');
    }




}
