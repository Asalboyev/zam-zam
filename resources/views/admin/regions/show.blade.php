@extends('layouts.admin')
@section('title')
    Show Advertising {{ $ads->id }}
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
                <h4>Advertising id {{ $ads->id }}</h4>
                <div class="card-header-form">
                    <a href="{{ route('admin.delivers.index') }}" class="btn btn-primary">Back</a>
                </div>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-md">
                        <tbody>
                            <tr>
                                <th> ID
                                <td>{{ $ads->id }}</td>
                                </th>
                            </tr>

                            <tr>
                                <th>Title (Top)
                                <td>{{ $ads->title_1 }}</td>
                                </th>
                            </tr>
                            <tr>
                                <th>Image (Top)
                                <td>{{ $ads->image_1 }}</td>
                                </th>
                            </tr>
                            <tr>
                                <th>Url (Top)
                                <td>{{ $ads->url_1 }}</td>
                                </th>
                            </tr>
                            <tr>
                                <th>Title (Sidebar)
                                <td>{{ $ads->title_2 }}</td>
                                </th>
                            </tr>
                            <tr>
                                <th>Image (Sidebar)
                                <td>{{ $ads->image_2 }}</td>
                                </th>
                            </tr>
                            <tr>
                                <th>Url (Sidebar)
                                <td>{{ $ads->url_2 }}</td>
                                </th>
                            </tr>


                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-right">
                <nav class="d-inline-block">
                    <ul class="pagination mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1"><i class="fas fa-chevron-left"></i></a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1 <span
                                    class="sr-only">(current)</span></a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">2</a>
                        </li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
@endsection
