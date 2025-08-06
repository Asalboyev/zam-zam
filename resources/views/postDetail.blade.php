@extends('layouts.site')

@section('title')
    Maqola Sahifasi -
@endsection

@section('content')
    <section class="article">
        <div class="container">
            <div class="news__wrapper basic-flex">
                <div class="article__wrapper">
                    <h2 class="article__title">
                        {{ $post['title_'.\App::getLocale()] }}
                    </h2>
                    <span class="article__date basic-flex"> {{ $post->created_at->format("H:m / d.m.Y") }}</span>
                    <img src="/site/images/posts/{{ $post->image }}" alt="Шавкат Мирзиёев строго предупредил хокимов всех уровней
          ">
                    {!! $post['body_'.\App::getLocale()] !!}

                    <div class="hashtags basic-flex">
                        @foreach ($post->tags as $tag)
                          <a href="#">#{{ $tag['name_'.\App::getLocale()] }}</a>
                        @endforeach


                    </div>
                </div>
                @include('sections.popularPosts')
                 <div class="related-news">
                    <h3 class="related-news__title">Новости по теме
                    </h3>
                    <div class="related-posts basic-flex">
                        @foreach ($otherPosts as $post)
                        <div class="posts__item">
                            <a href="{{ route('postDetail',$post->slug) }}">
                                <img src="/site/images/posts/{{ $post->image }}" alt="Image" class="posts__img">
                                <h2 class="posts__title">{{ $post['title_'.\App::getLocale()] }}</h2>
                                <span class="posts__date">{{ $post->created_at->format('H:i / d.m.Y') }}</span>
                            </a>
                        </div>

                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
