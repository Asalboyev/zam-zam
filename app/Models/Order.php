<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'customer_id',
        'order_date',
        'meal_1_id', 'meal_1_quantity',
        'meal_2_id', 'meal_2_quantity',
        'meal_3_id', 'meal_3_quantity',
        'meal_4_id', 'meal_4_quantity',
        'cola_quantity',
        'user_id',
        'delivery_fee',
        'driver_id',
        'payment_method',
        'total_meals',
        'received_amount',
        'daily_order_number',
        'total_amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    protected $casts = [
        'order_date' => 'date',
        'meal_1_quantity' => 'integer',
        'meal_2_quantity' => 'integer',
        'meal_3_quantity' => 'integer',
        'meal_4_quantity' => 'integer',
        'cola_quantity' => 'integer',
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function meals()
    {
        return $this->belongsToMany(Meal::class)->withPivot('quantity')->withTimestamps();
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function meal1()
    {
        return $this->belongsTo(Meal::class, 'meal_1_id');
    }

    public function meal2()
    {
        return $this->belongsTo(Meal::class, 'meal_2_id');
    }

    public function meal3()
    {
        return $this->belongsTo(Meal::class, 'meal_3_id');
    }

    public function meal4()
    {
        return $this->belongsTo(Meal::class, 'meal_4_id');
    }
}




    // Calculate total meals
//    public function getTotalMealsAttribute()
//    {
//        return $this->meal_1_quantity
//            + ($this->meal_2_quantity ?? 0)
//            + ($this->meal_3_quantity ?? 0)
//            + ($this->meal_4_quantity ?? 0);
//    }
//
//    // Formatted total amount
//    public function getFormattedTotalAttribute()
//    {
//        return number_format($this->total_amount, 0, ',', ' ');
//    }
//
//    // Payment method display
//    public function getPaymentMethodDisplayAttribute()
//    {
//        return match($this->payment_method) {
//            'naqt' => '💷 Naqt',
//            'karta' => '💳 Karta',
//            'transfer' => '↗️ O\'tkazma',
//            default => $this->payment_method,
//        };
//    }
//
//    // Automatically calculate total before saving
//    protected static function booted()
//    {
//        static::saving(function ($order) {
//            $total = 0;
//
//            // Calculate meals total
//            if ($order->meal1) {
//                $total += $order->meal1->price * $order->meal_1_quantity;
//            }
//            if ($order->meal2 && $order->meal_2_quantity) {
//                $total += $order->meal2->price * $order->meal_2_quantity;
//            }
//            if ($order->meal3 && $order->meal_3_quantity) {
//                $total += $order->meal3->price * $order->meal_3_quantity;
//            }
//            if ($order->meal4 && $order->meal_4_quantity) {
//                $total += $order->meal4->price * $order->meal_4_quantity;
//            }
//
//            // Add cola (assuming 10,000 per cola)
//            $total += $order->cola_quantity * 10000;
//
//            // Add delivery fee
//            $total += $order->delivery_fee;
//
//            $order->total_amount = $total;
//        });
//
//        // Update customer balance when order is created
//        static::created(function ($order) {
//            if ($order->payment_method === 'naqt') {
//                $order->customer->balance += $order->total_amount;
//                $order->customer->save();
//            }
//        });
//    }
//}
