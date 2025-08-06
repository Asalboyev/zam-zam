<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;


class DeliversController extends Controller
{
    public function index(){
        $drivers = Driver::latest()
            ->paginate(12);;
        return view('admin.delivers.index', compact('drivers'));
    }
    public function create(){
        return view('admin.delivers.create');
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:drivers,phone',
            'vehicle_type' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        Driver::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'vehicle_type' => $validated['vehicle_type'] ?? null,
            'vehicle_number' => $validated['vehicle_number'] ?? null,
//            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('admin.drivers.index')->with('success', 'Haydovchi muvaffaqiyatli qo‘shildi.');
    }

    public function edit(Driver $driver){
        return view('admin.delivers.edit', compact('driver'));
    }


    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:drivers,phone,' . $driver->id,
            'vehicle_type' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $driver->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'vehicle_type' => $validated['vehicle_type'] ?? null,
            'vehicle_number' => $validated['vehicle_number'] ?? null,
//            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('admin.drivers.index')->with('success', 'Haydovchi yangilandi.');
    }
    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()->route('admin.drivers.index')->with('success', 'Haydovchi o‘chirildi.');
    }






}
