@extends('layouts.admin')

@section('title')
    Mijoz o'zgartrish
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
    <style>
        .status-group.select-active select {
            border: 2px solid #28a745 !important;
            background-color: #e6f4ea !important;
            color: #155724;
            font-weight: bold;
        }
        #map {
            width: 100%;
            height: 350px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
    </style>


@endsection
@section('content')
    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-10 col-md-4 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Mijoz kartojkasi</h4>
                    </div>

                    <div class="card-body">
                        <div class="form-group status-group">
                            <label>Status</label>
                            <select class="form-control" name="status" >
                                <option value="Active" {{ $customer->status == 'Active' ? 'selected' : '' }}>Aktiv</option>
                                <option value="Blok" {{ $customer->status == 'Blok' ? 'selected' : '' }}>Blok</option>
                            </select>

                        </div>

                        <div class="form-group">
                            <label>Fio</label>
                            <input type="text" name="name" class="form-control"  placeholder="fio..." value="{{$customer->name}}">
                        </div>
                        <div class="form-group">
                            <label>Telefon</label>
                            <input type="text"  name="phone"  placeholder="telefon..." class="form-control" value="{{$customer->phone}}">
                        </div>
                        <div class="form-group">
                            <label>Telegram</label>
                            <input type="text"  name="telegram"  placeholder="telegram..." class="form-control" value="{{$customer->telegram}}">
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-10 col-md-4 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Manzil</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="map-coords">Karta lokatsiya</label>
                            <input type="text" name="location_coordinates" id="map-coords" class="form-control" value="{{ $customer->location_coordinates }}">
                            <div id="map" style="height: 350px; border-radius: 8px; margin-top: 10px;"></div>
                        </div>

                        <div class="form-group">
                            <label>Tuman</label>
                            <input type="text" name="address" class="form-control" value="{{$customer->address}}">
                        </div>
                        <div class="form-group">
                            <label>Manzil</label>
                            <input type="text" name="district" class="form-control" value="{{$customer->district}}">
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-10 col-md-4 col-lg-4">
                <div class="card">

                    <div class="card-header">
                        <h4>Oylik turi</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group status-group">
                            <label>Turini tanlang</label>
                            <select class="form-control" name="type" >
                                <option value="Oylik mijoz" {{ $customer->type == 'Oylik mijoz' ? 'selected' : '' }}>Oylik mijoz</option>
                                <option value="Odiy" {{ $customer->type == 'Odiy' ? 'selected' : '' }}>Odiy</option>
                            </select>

                        </div>
                        <div class="form-group">
                            <label>Balans</label>
                            <input type="text"  name="balance" class="form-control" value="{{ number_format($customer->balance, 3, '.', ' ') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center">
            <button class="btn btn-primary">Saqlash</button>
        </div>
        <form>
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

{{--                <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer></script>--}}

                <script>
                    let map;
                    let marker;

                    function dmsToDecimal(dmsStr) {
                        const regex = /(\d+)°(\d+)'([\d.]+)"?([NSEW])/g;
                        const matches = [...dmsStr.matchAll(regex)];
                        if (matches.length !== 2) return null;

                        const convert = ([_, deg, min, sec, dir]) => {
                            let decimal = parseFloat(deg) + parseFloat(min) / 60 + parseFloat(sec) / 3600;
                            if (dir === "S" || dir === "W") decimal *= -1;
                            return decimal;
                        };

                        return {
                            lat: convert(matches[0]),
                            lng: convert(matches[1])
                        };
                    }

                    function initMap() {
                        const coordsInput = document.getElementById('map-coords');
                        const value = coordsInput.value.trim();

                        let latLng = { lat: 41.311081, lng: 69.240562 }; // Default (Toshkent)

                        if (value.includes("°")) {
                            const converted = dmsToDecimal(value);
                            if (converted) latLng = converted;
                        } else if (value.includes(",")) {
                            const parts = value.split(',');
                            latLng = {
                                lat: parseFloat(parts[0]),
                                lng: parseFloat(parts[1])
                            };
                        }

                        map = new google.maps.Map(document.getElementById("map"), {
                            center: latLng,
                            zoom: 13,
                        });

                        marker = new google.maps.Marker({
                            position: latLng,
                            map: map,
                            draggable: true,
                        });

                        marker.addListener('dragend', function (e) {
                            const lat = e.latLng.lat().toFixed(6);
                            const lng = e.latLng.lng().toFixed(6);
                            coordsInput.value = `${lat}, ${lng}`;
                        });

                        map.addListener("click", (e) => {
                            const lat = e.latLng.lat().toFixed(6);
                            const lng = e.latLng.lng().toFixed(6);
                            marker.setPosition(e.latLng);
                            coordsInput.value = `${lat}, ${lng}`;
                        });
                    }
                </script>

                <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer></script>



@endsection

