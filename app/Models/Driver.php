<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'name', 'phone', 'vehicle_type',
        'vehicle_number', 'is_active'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
