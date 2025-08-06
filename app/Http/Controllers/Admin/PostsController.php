<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

use Str;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::All();
        return view('admin.products.index',compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $categories = Category::all();
        $tags = Tag::all();
        return view('admin.products.create',compact('categories','tags'));
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
            'title_uz' => 'required',
            'title_ru' => 'required',
            'category_id' => 'required',
            'body_uz' => 'required',
            'body_ru' => 'required',
       ]);

       $requestData = $request->all();

        if(empty($request->is_spacial)){
            $requestData['is_spacial'] = 0;
        }

       if($request->hasFile('image')){
            $file = $request->file('image');
            $image_name = time().'.'.$file->getClientOriginalExtension();
            $file->move('site/images/products/',$image_name);
            $requestData['image'] = $image_name;
       }

    //    /* $requestData['slug'] = Str::slug($request->title_uz);  */ // Post modelida saving method

       $post = Post::create($requestData);
       $post->tags()->attach($request->tags);
       return redirect()->route('admin.products.index')->with('success', 'Post created succuessfuly');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
       return view('admin.products.show',compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {


        $categories = Category::all();
        $tags = Tag::all();
        return view('admin.products.edit',compact('categories','post','tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title_uz' => 'required',
            'title_ru' => 'required',
            'category_id' => 'required',
            'body_uz' => 'required',
            'body_ru' => 'required',
       ]);

       $requestData = $request->all();

       if(empty($request->is_spacial)){
        $requestData['is_spacial'] = 0;
    }

       if($request->hasFile('image')){
            $file = $request->file('image');
            $image_name = time().'.'.$file->getClientOriginalExtension();
            $file->move('site/images/products/',$image_name);
            $requestData['image'] = $image_name;
       }

    //    $requestData['slug'] = Str::slug($request->title_uz);  // Post modelida saving method

       $post->update($requestData);
       $post->tags()->sync($request->tags);

       return redirect()->route('admin.products.index')->with('success', 'Post updated succuessfuly');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Post::destroy($id);
        return redirect()->route('admin.products.index')->with('success', 'Post deleted succuessfuly');

    }

    public function upload(Request $request)
    {
        if($request->hasFile('upload')) {
            $fileName = $request->file('upload')->getClientOriginalName();
            // $fileName = pathinfo($originName, PATHINFO_FILENAME);
            // $extension = $request->file('upload')->getClientOriginalExtension();
            // $fileName = $fileName.'_'.time().'.'.$extension;
            $request->file('upload')->move('site/images/products/', $fileName);
            $CKEditorFuncNum = $request->input('CKEditorFuncNum');
            $url = asset('images/'.$fileName);
            $msg = 'Image successfully uploaded';
            $response = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";

            @header('Content-type: text/html; charset=utf-8');
            echo $response;
        }
    }
}
