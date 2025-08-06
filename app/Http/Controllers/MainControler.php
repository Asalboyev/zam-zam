<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Post;

class MainControler extends Controller
{


    public function index ()
    {

        $specialPosts = Post::where('is_spacial',1)->limit(6)->latest()->get();
        $latestPosts = Post::limit(6)->latest()->get();
        $popularPosts = Post::limit(4)->orderBy('view','DESC')->get();
        return view('index',compact('specialPosts','latestPosts','popularPosts'));
    }

    public function categoryPosts ($slug)
    {


        $category = Category::where('slug',$slug)->first();
        return view('categoryPosts',compact('category'));
    }
    public function postDetail ($slug)
    {

        $post  = Post::where('slug',$slug)->first();
        $post->increment('view');
        $post->save();

        $otherPosts = Post::where('category_id',$post->category_id)->where('id', '!=', $post->id)->limit(3)->get();

        $popularPosts = Post::limit(4)->orderBy('view','DESC')->get();
        return view('postDetail',compact('post','popularPosts','otherPosts'));
    }
    public function contact ()
    {

        return view('contact');
    }
    public function search(Request $request)
    {
        $key = $request->key;

        $popularPosts = Post::limit(4)->orderBy('view','DESC')->get();
        $posts = Post::where('title_uz','like','%'.$key.'%')
                ->orWhere('title_ru','like','%'.$key.'%')
                ->orWhere('body_uz','like','%'.$key.'%')
                ->orWhere('body_ru','like','%'.$key.'%')->get() ;

        return view('search',compact('popularPosts','key','posts'));

    }
}
