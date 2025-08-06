@extends('layouts.site')
@section('title')
        Yangliklar Sahifasi -
@endsection

@section('content')
    <section class="news">
        <div class="container">
            <div class="news__wrapper basic-flex">
                <div class="column-news">
                    <h2 class="news__title">{{ $category['name_'.\App::getLocale()] }} yangiliklar</h2>
                    <ul class="news__list basic-flex">
                        @foreach ($category->posts as $post)
                        <li class="news__item">
                            <a href="#" class="basic-flex news__link">
                                <div class="news-image-wrapper"><img src="/img/bottom1.png" alt="Bottom Img"></div>
                                <div class="news-box basic-flex">
                                    <h4 class="news__title">
                                        {{ $post['title_'.\App::getLocale()] }}
                                    </h4>
                                    <p class="news__description">
                                        {!! \Str::limit($post['body_'.\App::getLocale()],100) !!}
                                    </p>
                                    <span class="news__date basic-flex">{{ $post->created_at->format('H:i / d.m.Y') }}</span>
                                </div>
                            </a>
                        </li>
                        @endforeach


                    </ul>
                    <button type="button" class="btn load-more-btn">Больше новостей</button>
                </div>
                <div class="popular-news">
                    <div class="popular-news-wrapper">
                        <h4 class="popular-news__title">Cамые популярные новости</h4>
                        <ul class="popular-news__list">
                            <li class="popular-news__item">
                                <a href="#">
                                    <p class="popular-news__description">По факту прорыва Сардобинского водохранилища
                                        возбуждено уголовное дело</p>
                                    <span class="popular-news__date">11:31 / 15.05.2020</span>
                                </a>
                            </li>
                            <li class="popular-news__item">
                                <a href="#">
                                    <p class="popular-news__description">По факту прорыва Сардобинского водохранилища
                                        возбуждено уголовное дело</p>
                                    <span class="popular-news__date">11:31 / 15.05.2020</span>
                                </a>
                            </li>
                            <li class="popular-news__item">
                                <a href="#">
                                    <p class="popular-news__description">По факту прорыва Сардобинского водохранилища
                                        возбуждено уголовное дело</p>
                                    <span class="popular-news__date">11:31 / 15.05.2020</span>
                                </a>
                            </li>
                            <li class="popular-news__item">
                                <a href="#">
                                    <p class="popular-news__description">По факту прорыва Сардобинского водохранилища
                                        возбуждено уголовное дело</p>
                                    <span class="popular-news__date">11:31 / 15.05.2020</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="ads-placeholder">
                        <h4>ADS PLACEHOLDER</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
