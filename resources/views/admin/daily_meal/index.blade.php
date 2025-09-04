@extends('layouts.admin')
@section('title', 'Kunlik mahsulot')

@section('css')
    <style>
        .toggle-btn {
            background: #4a4af4;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .meal-list.collapse.inactive {
            display: none;
        }

        .meal-list.collapse.active {
            display: block;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
            margin-bottom: 20px;
        }
        .header-section h2 {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .btn-primary-custom {
            background: #4a4af4;
            color: white;
            padding: 8px 16px;
            font-size: 14px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-primary-custom:hover {
            background: #3737d1;
        }

        /* Weekly group container */
        .date-group {
            border: 2px dotted #4a90e2;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 0;
            background: white;
            overflow: hidden;
        }

        /* Date header */
        .date-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f4f6ff;
            padding: 12px 16px;
            border-bottom: 1px solid #ddd;
        }
        .date-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .today-btn {
            background: #00c853;
            color: white;
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 6px;
        }
        .date-header h3 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }
        .add-btn {
            background: #4a4af4;
            color: white;
            padding: 6px 14px;
            font-size: 13px;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .add-btn:hover {
            background: #3737d1;
        }

        /* Meal items */
        .meal-card {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }
        .meal-card:last-child {
            border-bottom: none;
        }
        .meal-card:hover {
            background: #f9f9f9;
        }
        .meal-image img {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #ddd;
        }
        .meal-details {
            flex: 1;
            margin-left: 15px;
        }
        .meal-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .meal-stats {
            font-size: 13px;
            color: #555;
        }
        .meal-action a {
            background: #e0e0e0;
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            color: #333;
        }
        .meal-action a:hover {
            background: #ccc;
        }
    </style>
@endsection

@section('content')
    <div class="col-12 col-md-12 col-lg-12">
        {{-- Header --}}
        <div class="header-section">
            <h2>Kunlik reja yaratish</h2>
            <a href="{{ route('admin.daily_meal.create') }}" class="btn-primary-custom">+ Yaratish</a>
        </div>

        {{-- Success message --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible show fade">
                <div class="alert-body">
                    <button class="close" data-dismiss="alert"><span>×</span></button>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        {{-- Weekly search form --}}
        <form method="GET" action="{{ route('admin.daily_meal.index') }}" class="mb-4">
            <div class="form-group row">
                <label for="date" class="col-form-label col-md-2">Sanani tanlang:</label>
                <div class="col-md-4">
                    <input type="date" name="date" id="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Hafta bo‘yicha qidirish</button>
                </div>
            </div>
        </form>

        {{-- Meals grouped by day --}}
        @if($dailyMeals->isNotEmpty())
            <div class="daily-meals">
                @foreach($dailyMeals as $day => $meals)
                    <div class="date-group">
                        <div class="date-header">
                            <div class="date-header-left">
                                @if(\Carbon\Carbon::parse($day)->isToday())
                                    <span class="today-btn">Bugun</span>
                                @endif
                                <h3>{{ \Carbon\Carbon::parse($day)->translatedFormat('d F Y') }}</h3>
                                    <a href="{{ route('admin.daily_meal.create') }}" class="btn-primary-custom">+ Yaratish</a>

                            </div>

                            <button class="toggle-btn" data-target="day-{{ $loop->index }}">
                                ↓
                            </button>
                        </div>

                        {{-- Meal Items --}}
                        @if(\Carbon\Carbon::parse($day)->isToday())
                            <div class="meal-list collapse active" id="day-{{ $loop->index }}">
                                @foreach($meals as $meal)
                                    @foreach($meal->items as $item)
                                        <div class="meal-card">
                                            <div class="meal-image">
                                                @if($item->image)
                                                    <img src="{{ asset('uploads/' . $item->image) }}" alt="{{ $item->name }}">
                                                @else
                                                    <span class="text-muted">Rasm yo'q</span>
                                                @endif
                                            </div>
                                            <div class="meal-details">
                                                <p class="meal-name">{{ $item->name }}</p>
                                                <p class="meal-stats">
                                                    Olindi: <strong>{{ $item->pivot->remaining_count}}</strong> |
                                                    Qoldi: <strong>{{ $item->pivot->count}}</strong>
                                                </p>
                                            </div>
                                            <div class="meal-action">
                                                <a href="{{ route('admin.daily_meal.edit', $item->pivot->id) }}">✏️</a>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        @else
                            <div class="meal-list collapse inactive" id="day-{{ $loop->index }}">
                                @foreach($meals as $meal)
                                    @foreach($meal->items as $item)
                                        <div class="meal-card">
                                            <div class="meal-image">
                                                @if($item->image)
                                                    <img src="{{ asset('uploads/' . $item->image) }}" alt="{{ $item->name }}">
                                                @else
                                                    <span class="text-muted">Rasm yo'q</span>
                                                @endif
                                            </div>
                                            <div class="meal-details">
                                                <p class="meal-name">{{ $item->name }}</p>
                                                <p class="meal-stats">
                                                    Olindi: <strong>{{ $item->pivot->count }}</strong> |
                                                    Qoldi: <strong>{{ $item->pivot->remaining_count }}</strong>
                                                </p>
                                            </div>
                                            <div class="meal-action">
                                                <a href="{{ route('admin.daily_meal.edit', $item->pivot->id) }}">✏️</a>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>

                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-warning">Tanlangan hafta uchun ovqatlar topilmadi.</div>
        @endif
    </div>
@endsection
@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButtons = document.querySelectorAll('.toggle-btn');

            toggleButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-target');
                    const content = document.getElementById(targetId);

                    // Klasslarni almashtirish
                    content.classList.toggle('active');
                    content.classList.toggle('inactive');

                    // Tugma matnini almashtirish
                    if (content.classList.contains('active')) {
                        this.textContent = ' ↑';
                    } else {
                        this.textContent = '↓';
                    }
                });
            });
        });
    </script>

@endsection
