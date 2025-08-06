@extends('layouts.admin')

@section('title')
    Mijoz qo'shish
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
    <form action="{{ route('admin.customers.store') }}" method="POST">
        @csrf
    <div class="row">
    <div class="col-10 col-md-4 col-lg-4">
            <div class="card">
                    <div class="card-header">
                        <h4>Mijoz kartojkasi</h4>
                    </div>

                    <div class="card-body">
                        <div class="form-group status-group">
                            <label>Status</label>
                            <select class="form-control " name="status" required>
                                <option value="Active">Aktiv</option>
                                <option value="Blok">Blok</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Fio</label>
                            <input type="text" name="name" class="form-control" required="" placeholder="fio...">
                        </div>
                        <div class="form-group">
                            <label>Telefon</label>
                            <input type="text"  name="phone"  placeholder="telefon..." class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Telegram</label>
                            <input type="text"  name="telegram"  placeholder="telegram..." class="form-control">
                        </div>
                    </div>
{{--                    <div class="card-footer text-right">--}}
{{--                        <button class="btn btn-primary">Submit</button>--}}
{{--                    </div>--}}
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
                            <input type="text" name="location_coordinates" id="map-coords" class="form-control" placeholder="Masalan: 41°21'03.3&quot;N 69°13'20.2&quot;E">
                            <div id="map" style="height: 350px; border-radius: 8px; margin-top: 10px;"></div>
                        </div>

                        <div class="form-group">
                            <label>Tuman</label>
                            <input type="text" name="address" class="form-control" required="">
                        </div>
                        <div class="form-group">
                            <label>Manzil</label>
                            <input type="text" name="district" class="form-control" required="">
                        </div>
                    </div>
{{--                    <div class="card-footer text-right">--}}
{{--                        <button class="btn btn-primary">Submit</button>--}}
{{--                    </div>--}}
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
                                <select class="form-control " name="type" required>
                                    <option value="Oylik mijoz">Oylik mijoz</option>
                                    <option value="Odiy">Odiy</option>
                                </select>
                            </div>
                        <div class="form-group">
                            <label>Balans</label>
                            <input type="text"  name="balance" class="form-control" required="">
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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDBkiXl9DlEXU2LsI6aZjvUYGEDTHke4ok"></script>

    <script>
        let map;
        let marker;

        function initMap() {
            const defaultLoc = { lat: 41.3111, lng: 69.2797 }; // Toshkent markazi
            map = new google.maps.Map(document.getElementById("map"), {
                center: defaultLoc,
                zoom: 13
            });

            marker = new google.maps.Marker({
                position: defaultLoc,
                map: map,
                draggable: true
            });

            // Inputga yozilganda marker harakatlanadi
            document.getElementById("map-coords").addEventListener("change", function () {
                const val = this.value.trim();
                const parts = val.split(",");
                if (parts.length === 2) {
                    const lat = parseFloat(parts[0]);
                    const lng = parseFloat(parts[1]);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const newLoc = { lat, lng };
                        marker.setPosition(newLoc);
                        map.panTo(newLoc);
                    }
                }
            });

            // Xarita bosilganda input yangilanadi
            map.addListener("click", function (e) {
                const lat = e.latLng.lat().toFixed(6);
                const lng = e.latLng.lng().toFixed(6);
                const latLng = { lat: parseFloat(lat), lng: parseFloat(lng) };

                marker.setPosition(latLng);
                map.panTo(latLng);
                document.getElementById("map-coords").value = `${lat}, ${lng}`;
            });
        }

        window.onload = initMap;
    </script>


@endsection

