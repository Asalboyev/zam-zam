<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Post;

class MainControler extends Controller
{


    public function stats(Request $request)
    {
        $from = $request->input('from', '2023-01-01');
        $to = $request->input('to', now()->format('Y-m-d'));

        $orders = \DB::table('orders')
            ->selectRaw('YEAR(order_date) as year, MONTH(order_date) as month, COUNT(*) as total_orders, SUM(total_meals) as total_meals')
            ->whereBetween('order_date', [$from, $to])
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact('orders', 'from', 'to'));
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
