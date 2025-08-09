@extends('layouts.admin')

@section('title')
    Tuman qo'shish
@endsection
@section('content')
    <div class="col-12 col-md-1 col-lg-12">
        <form action="{{ route('admin.regions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
        <div class="card">
            <div class="card-header">
                <h4>Tuman qo'shish </h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Tuman nomi</label>
                    <input type="text" class="form-control" name="name" @error('name')  is-invalid @enderror placeholder="Uchtepa...">
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
