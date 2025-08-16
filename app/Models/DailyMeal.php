<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyMeal extends Model
{
    protected $fillable = ['date'];


    public function items()
    {
        return $this->belongsToMany(Meal::class, 'daily_meal_items', 'daily_meal_id', 'meal_id')
            ->withPivot('count')
            ->withPivot('remaining_count')
            ->withPivot('sell')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function meal()
    {
        return $this->belongsTo(DailyMeal::class, 'daily_meal_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
