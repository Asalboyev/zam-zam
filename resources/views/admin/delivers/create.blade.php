@extends('layouts.admin')

@section('title')
    Create Advertising
@endsection
@section('content')
    <div class="col-12 col-md-1 col-lg-12">
        <form action="{{ route('admin.drivers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
        <div class="card">
            <div class="card-header">
                <h4>Haydovchi</h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Fio</label>
                    <input type="text" class="form-control" name="name" @error('name')  is-invalid @enderror>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Telfon raqam</label>
                    <input type="text" class="form-control" name="phone" @error('phone')  is-invalid @enderror>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Moshina turi</label>
                    <input type="text" class="form-control" name="vehicle_type" @error('vehicle_type')  is-invalid @enderror>
                    @error('vehicle_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>  <div class="form-group">
                    <label>Avtomobil raqami</label>
                    <input type="text" class="form-control" name="vehicle_number" @error('vehicle_number')  is-invalid @enderror>
                    @error('vehicle_number')
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
