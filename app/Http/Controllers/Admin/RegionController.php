<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    public function index()
    {
        $regions = DB::table('regions')->orderByDesc('id')->get();
        return view('admin.regions.index', compact('regions'));
    }

    public function create()
    {
        return view('admin.regions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:regions,name',
        ]);


        DB::table('regions')->insert([
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.regions.index')->with('success', 'Viloyat qo‘shildi.');
    }

    public function edit($id)
    {
        $region = DB::table('regions')->where('id', $id)->first();
        return view('admin.regions.edit', compact('region'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:regions,name,' . $id,
        ]);

        DB::table('regions')->where('id', $id)->update([
            'name' => $request->name,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.regions.index')->with('success', 'Viloyat yangilandi.');
    }

    public function destroy($id)
    {
        DB::table('regions')->where('id', $id)->delete();
        return redirect()->route('admin.regions.index')->with('success', 'Viloyat o‘chirildi.');
    }
}
