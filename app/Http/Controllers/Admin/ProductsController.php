<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meal;
use Illuminate\Support\Str;
use function Symfony\Component\String\b;
use Illuminate\Support\Facades\File;



class ProductsController extends Controller
{
    public function index(){
        $meals = Meal::latest()
            ->paginate(12);
        return view('admin.products.index', compact('meals'));
    }
    public function create(){
        return view('admin.products.create');
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        Meal::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image' => $validated['image'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Taom qo‘shildi!');
    }

    public function edit(Meal $product){
        return view('admin.products.edit', compact('product'));
    }
    public function update(Request $request, Meal $product)
    {
//        dd($request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image' => $validated['image'] ?? $product->image,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Taom yangilandi!');
    }


    public function image_upload(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $filename = time() . '_' . \Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);

            return response()->json(['success' => true, 'filename' => $filename]);
        }

        return response()->json(['success' => false, 'message' => 'Fayl topilmadi.']);
    }
    public function delete(Request $request)
    {
        $fileName = $request->input('fileName');
        $filePath = public_path('uploads/' . $fileName); // sizda upload path boshqacha bo‘lishi mumkin

        if (file_exists($filePath)) {
            unlink($filePath);
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'File not found'], 404);
    }

    public function destroy($id)
    {
        $meal = Meal::findOrFail($id);

        // Rasmni ham o'chirish (agar mavjud bo‘lsa)
        if ($meal->image && file_exists(public_path('uploads/' . $meal->image))) {
            File::delete(public_path('uploads/' . $meal->image));
        }

        // Ma'lumotni bazadan o'chirish
        $meal->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Taom muvaffaqiyatli o‘chirildi.');
    }

}
