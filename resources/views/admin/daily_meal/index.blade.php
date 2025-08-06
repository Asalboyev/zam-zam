@extends('layouts.admin')
@section('title')
     Tags
@endsection
@section('content')

<div class="col-12 col-md-12 col-lg-12">

    <div class="card">

        @if (session('success'))
        <div class="alert alert-success alert-dismissible show fade">
            <div class="alert-body">
            <button class="close" data-dismiss="alert">
                <span>×</span>
            </button>
            {{ session('success') }}
            </div>
        </div>
    @endif
      <div class="card-header">
        <h4>Tags table</h4>
        <div class="card-header-form">
            <a href="{{ route('admin.daily_meal.create') }}" class="btn btn-primary">Create</a>
        </div>

      </div>
      <div class="card-body">
        <div class="table-responsive">
            {{-- Sana bo‘yicha qidirish formi --}}
            <form method="GET" action="{{ route('admin.daily_meal.index') }}" class="mb-4">
                <div class="form-group row">
                    <label for="date" class="col-form-label col-md-2">Sanani tanlang:</label>
                    <div class="col-md-4">
                        <input type="date"
                               name="date"
                               id="date"
                               class="form-control"
                               value="{{ request('date') ?? \Carbon\Carbon::today()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Qidirish</button>
                    </div>
                </div>
            </form>


            {{-- Faqat tanlangan sana uchun 4 ta ovqatni chiqarish --}}
            @if($dailyMeals)
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Rasm</th>
                        <th>Ovqat nomi</th>
                        <th>Son</th>
                        <th></th>
                    </tr>
                    <tbody>
                    @foreach($dailyMeals as $dailyMeal)
                        @foreach($dailyMeal->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($item->image)
                                        <img src="{{ asset('uploads/' . $item->image) }}" alt="Rasm" width="70" height="70" style="object-fit: cover; border-radius: 6px;">
                                    @else
                                        <span class="text-muted">Rasm yo‘q</span>
                                    @endif
                                </td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->pivot->count }}</td> {{-- Bu yerda count chiqadi --}}
                                <td>
                                    <a href="{{ route('admin.daily_meal.edit', $item->id) }}" class="icon-btn">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                    @endforeach


                    </tbody>
                </table>
            @else
                <div class="alert alert-warning">Tanlangan sana bo‘yicha ovqatlar topilmadi.</div>
            @endif
        </div>
      </div>
      <div class="card-footer text-right">
        <nav class="d-inline-block">
{{--            {{ $dailyMeals->links() }}--}}

        </nav>
      </div>
    </div>
  </div>
@endsection
