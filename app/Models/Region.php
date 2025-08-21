<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regions';
    use HasFactory;
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
}
