<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrdersMealController extends Controller
{
    public function index(){
        return view('orders.create', [
            'meals' => Meal::all(),
            'customers' => Customer::all(),
            'drivers' => Driver::all()
        ]);
    }
}
