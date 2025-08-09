@extends('layouts.admin')

@section('title')
    Tuman o'zgartrish
@endsection
@section('content')
    <div class="col-12 col-md-1 col-lg-12">
        <form action="{{ route('admin.regions.update',$region->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h4>Tuman qo'shish </h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tuman nomi</label>
                        <input type="text" class="form-control" name="name" @error('name')  is-invalid @enderror value="{{$region->name}}" >
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Saqlash</button>
                    </div>
                </div>

            </div>
        </form>

    </div>
@endsection
