<div class="popular-news">
    <div class="popular-news-wrapper">
        <h4 class="popular-news__title">Cамые популярные новости</h4>
        <ul class="popular-news__list">
            @foreach ($popularPosts as $popularPost)
            <li class="popular-news__item">
                <a href="#">
                    <p class="popular-news__description">  {!! \Str::limit($popularPost['body_'.\App::getLocale()],100) !!}</p>
                    <span class="popular-news__date">{{ $popularPost->created_at->format('H:i / d.m.Y') }}</span>
                </a>
            </li>
            @endforeach


        </ul>
    </div>
    {{-- <div class="delivers-placeholder">
        <h4>ADS PLACEHOLDER</h4>
    </div> --}}

    <a href="{{ $ad->url_2 }}" class="ads-placeholder" style="background-image: url(/site/images/ads/{{ $ad->image_2 }});">
        <h4>{{ $ad->title_2 }}</h4>
    </a>

</div>
