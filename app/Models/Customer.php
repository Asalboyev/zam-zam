<?php

namespace App\Models;

use App\Models\BalanceHistory;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'telegram',
        'status',
        'type',
        'address',
        'district',
        'location_coordinates',
        'balance',
        'balance_due_date'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'balance_due_date' => 'date',
    ];

    protected $appends = ['formatted_balance']; // Yangi qo'shilgan

    public function orders()
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 0, ',', ' ');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeMonthly($query)
    {
        return $query->where('type', 'monthly');
    }

    public function balanceHistories()
    {
        return $this->hasMany(BalanceHistory::class)->latest();
    }
}
