<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();


        view()->composer('layouts.site',function($view){
            $categories = \App\Models\Category::all();
            $ad = \App\Models\Ad::first();
            $view->with(compact('categories','ad'));
        });



        view()->composer('sections.popularPosts',function($view){
            $ad = \App\Models\Ad::first();
            $popularPosts = \App\Models\Post::limit(4)->orderBy('view','DESC')->get();
            $view->with(compact('popularPosts','ad'));
        });
    }
}
