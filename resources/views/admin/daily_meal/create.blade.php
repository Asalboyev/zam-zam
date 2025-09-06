@extends('layouts.admin')

@section('title')
    Create Tag
@endsection
@section('css')
    <link rel="stylesheet" href="/admin/assets/css/app.min.css">
    <link rel="stylesheet" href="/admin/assets/bundles/bootstrap-daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="/admin/assets/bundles/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="/admin/assets/bundles/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="/admin/assets/bundles/jquery-selectric/selectric.css">
    <link rel="stylesheet" href="/admin/assets/bundles/bootstrap-timepicker/css/bootstrap-timepicker.min.css">
    <link rel="stylesheet" href="/admin/assets/bundles/bootstrap-tagsinput/dist/bootstrap-tagsinput.css">
    <!-- Template CSS -->
    <link rel="stylesheet" href="/admin/assets/css/style.css">
    <link rel="stylesheet" href="/admin/assets/css/components.css">
@endsection
@section('content')
    <div class="col-12 col-md-1 col-lg-12">
        <form action="{{ isset($item) ? route('admin.daily_meal.item_update', $item->id) : route('admin.daily_meal.store') }}" method="POST">
            @csrf
            @if(isset($item))
                @method('PUT')
            @endif

            <div class="card">
                <div class="card-header">
                    <h4>Kunlik Maxsulot</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Soni</label>
                        <input class="form-control" type="date" name="date" value="{{  request('order_date', now()->format('Y-m-d')), old('date', isset($item->date) ? $item->date : '')  }}">

                    </div>
                    <div class="form-group">
                        <label>Ovqat nomi</label>
                        <select class="form-control select2" name="meal_id" required>
                            @foreach($meals as $meal)
                                <option value="{{ $meal->id }}"
                                    {{ (isset($selected) && in_array($meal->id, $selected)) ? 'selected' : '' }}>
                                    {{ $meal->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Soni</label>
                        <input type="number"
                               name="count"
                               class="form-control"
                               min="1"
                               step="1"
                               required
                               pattern="[1-9][0-9]*"
                               oninput="validity.valid||(value='');"
                               >
                    </div>

                </div>

                <div class="card-footer text-right">
                    <button class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('js')
    <script src="/admin/assets/js/app.min.js"></script>
    <!-- JS Libraies -->
    <script src="/admin/assets/bundles/cleave-js/dist/cleave.min.js"></script>
    <script src="/admin/assets/bundles/cleave-js/dist/addons/cleave-phone.us.js"></script>
    <script src="/admin/assets/bundles/jquery-pwstrength/jquery.pwstrength.min.js"></script>
    <script src="/admin/assets/bundles/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script src="/admin/assets/bundles/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
    <script src="/admin/assets/bundles/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
    <script src="/admin/assets/bundles/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
    <script src="/admin/assets/bundles/select2/dist/js/select2.full.min.js"></script>
    <script src="/admin/assets/bundles/jquery-selectric/jquery.selectric.min.js"></script>
    <!-- Page Specific JS File -->
    <script src="/admin/assets/js/page/forms-advanced-forms.js"></script>
    <!-- Template JS File -->
    <script src="/admin/assets/js/scripts.js"></script>
    <!-- Custom JS File -->
    <script src="/admin/assets/js/custom.js"></script>
    <!-- Google Maps JS API yuklash (key bilan) -->
@endsection
