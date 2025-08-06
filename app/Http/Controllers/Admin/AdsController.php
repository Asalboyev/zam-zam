<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ads = Ad::first();
        // dd($delivers);
        return view('admin.delivers.index', compact('ads'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.delivers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title_1' => 'required',
            'image_1' => 'required',
            'url_1' => 'required',
            'title_2' => 'required',
            'image_2' => 'required',
            'url_2' => 'required',
        ]);

        $requestData = $request->all();

        if ($request->hasFile('image_1')) {
            $file = $request->file('image_1');
            $image_name = time() . '.' . $file->getClientOriginalExtension();
            $file->move('site/images/delivers/', $image_name);
            $requestData['image_1'] = $image_name;
        }

        if ($request->hasFile('image_2')) {
            $file = $request->file('image_2');
            $image_name = time() . '2.' . $file->getClientOriginalExtension();
            $file->move('site/images/delivers/', $image_name);
            $requestData['image_2'] = $image_name;
        }

        //    /* $requestData['slug'] = Str::slug($request->title_uz);  */ // Post modelida saving method

        Ad::create($requestData);
        return redirect()->route('admin.delivers.index')->with('success', 'Advertising  created succuessfuly');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        $ads = Ad::find($id);
        return view('admin.delivers.show',compact('ads'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $ads = Ad::first();
        return view('admin.delivers.edit',compact('ads'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ad $ads)

    {

        // dd($delivers);
        $request->validate([
            'title_1' => 'required',
            'image_1' => 'required',
            'url_1' => 'required',
            'title_2' => 'required',
            'image_2' => 'required',
            'url_2' => 'required',
        ]);

        $requestData = $request->all();

        if ($request->hasFile('image_1')) {
            $file = $request->file('image_1');
            $image_name = time() . '.' . $file->getClientOriginalExtension();
            $file->move('site/images/delivers/', $image_name);
            $requestData['image_1'] = $image_name;
        }

        if ($request->hasFile('image_2')) {
            $file = $request->file('image_2');
            $image_name = time() . '2.' . $file->getClientOriginalExtension();
            $file->move('site/images/delivers/', $image_name);
            $requestData['image_2'] = $image_name;
        }

        //    /* $requestData['slug'] = Str::slug($request->title_uz);  */ // Post modelida saving method

        $ads->update($requestData);
        return redirect()->route('admin.delivers.index')->with('success', 'Advertising  update succuessfuly');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ad $ad)
    {
        $ad->delete();
        return redirect()->route('admin.delivers.index')->with('success','Advertising deleted successfully!');
    }
}
