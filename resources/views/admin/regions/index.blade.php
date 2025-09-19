@extends('layouts.admin')
@section('title')
    Regions
@endsection
@section('content')

<div class="col-12 col-md-12 col-lg-12">

    <div class="row">
        <div class="col-5 mb-3">
            <div class="card mb-0">
                <div class="card-body">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link " href="{{ route('admin.products.index') }}">Ovqatlar <span class="badge badge-white"></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link  active" href="{{ route('admin.regions.index') }}">Tuman <span class="badge badge-primary"></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="{{ route('admin.drivers.index') }}">Kuryerlar <span class="badge badge-primary"></span></a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link " href="{{route('admin.dashboard')}}">Kassa <span class="badge badge-primary"></span></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
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
        <h4>Haydovchila </h4>
        <div class="card-header-form">
            {{-- @empty($delivers) --}}
            <a href="{{ route('admin.regions.create') }}" class="btn btn-primary">Qo'shish </a>
            {{-- @endempty --}}
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-md">
            <tbody><tr>
              <th>#</th>
              <th>Name</th>
              <th>Action</th>
            </tr>
             @foreach ($regions as $ad)
            <tr>
                <td>{{$loop->iteration }}</td>
                <td>{{$ad->name}}</td>
                <td>
                    <form style="display: inline" action="{{ route('admin.regions.destroy',$ad->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button href="#" class="btn btn-danger" onclick="return confirm('Ochirishni xohlisizmi?')" type="submit">Delete</button>
                    </form>
                    <a href="{{ route('admin.regions.edit',$ad->id) }}" class="btn btn-success">Edit</a>
                </td>
              </tr>
             @endforeach
          </tbody></table>
        </div>
      </div>
      <div class="card-footer text-right">
        <nav class="d-inline-block">
            {{-- {{ $customers->links() }} --}}
          {{-- <ul class="pagination mb-0">
            <li class="page-item disabled">
              <a class="page-link" href="#" tabindex="-1"><i class="fas fa-chevron-left"></i></a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1 <span class="sr-only">(current)</span></a></li>
            <li class="page-item">
              <a class="page-link" href="#">2</a>
            </li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
              <a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>
            </li>
          </ul> --}}
        </nav>
      </div>
    </div>
  </div>
@endsection
