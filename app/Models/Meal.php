<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Meal extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name', 'description', 'price', 'is_active','image'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('meal_images')
            ->singleFile();
    }

//    public function dailyMeals()
//    {
//        return $this->belongsToMany(DailyMeal::class, 'daily_meal_items');
//    }
    public function dailyMeals()
    {
        return $this->belongsToMany(DailyMeal::class, 'daily_meal_items', 'meal_id', 'daily_meal_id')
            ->withPivot('count');
    }

}
