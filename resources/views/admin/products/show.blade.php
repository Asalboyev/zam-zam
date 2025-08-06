@extends('layouts.admin')
@section('title')
     Show
@endsection

@section('css')
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
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
        <h4>Show table  Id -> {{ $post->id }}</h4>
        <div class="card-header-form">
            <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
        </div>

      </div>
      <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="table-1">
                <tr>
                    <th>Title (UZ)</th> <td>{{ $post->title_uz }}</td>
                </tr>
                <tr>
                    <th>Title (RU)</th> <td>{{ $post->title_ru }}</td>
                </tr>
                <tr>
                    <th>Body (UZ)</th> <td>{!! $post->body_uz !!}</td>
                </tr>
                <tr>
                    <th>Body (RU)</th> <td>{!! $post->body_ru !!}</td>
                </tr>
                <tr>
                    <th>Category</th> <td>{!! $post->category->name_uz !!}</td>
                </tr>
                <tr>
                    <th>Tag</th> <td> @foreach ($post->tags as $tag)
                        {{ $tag->name_uz }}
                    @endforeach </td>
                </tr>
                <tr>
                    <th>Image</th> <td ><img src="/site/images/posts/{{ $post->image }}" width="100px" height="100px" alt=""></td>
                </tr>
                <tr>
                    <th>Slug</th> <td>{{ $post->slug }}</td>
                </tr>
                <tr>
                    <th>Meta title</th> <td>{{ $post->meta_title }}</td>
                </tr>
                <tr>
                    <th>Meta description</th> <td>{{ $post->meta_description }}</td>
                </tr>
                <tr>
                    <th>Meta keywords</th> <td>{{ $post->meta_keywords }}</td>
                </tr>
            </table>
          </div>
      </div>
      <div class="card-footer text-right">
        <nav class="d-inline-block">
            {{-- {{ $customers->links() }} --}}
        </nav>
      </div>
    </div>
  </div>
@endsection

@section('js')
    <script src="assets/bundles/datatables/datatables.min.js"></script>
    <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/page/datatables.js"></script>

@endsection
