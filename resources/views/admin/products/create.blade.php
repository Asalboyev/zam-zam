@extends('layouts.admin')

@section('title')
    Maxsulot qo'shish
@endsection
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Defaultda X tugmasini yashiramiz */
        .dz-remove {
            display: none;
            position: absolute;
            top: 5px;
            right: 5px;
            background: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 14px;
            font-weight: bold;
            line-height: 22px;
            text-align: center;
            color: red;
            box-shadow: 0 0 3px rgba(0,0,0,0.3);
            cursor: pointer;
            z-index: 999;
        }

        /* Rasm hover bo‘lganda X tugmasi chiqsin */
        .dz-preview:hover .dz-remove {
            display: block;
        }
    </style>


@endsection
@section('content')
    <div class="col-12 col-md-1 col-lg-12">
        <form action="{{ route('admin.products.store') }}" id="mealForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <form class="needs-validation" novalidate="">
                    <div class="card-header">
                        <h4>Maxsulot </h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Nomi</label>
                            <input type="text"  name="name" class="form-control" required="">

                        </div>
                        <div class="form-group status-group">
                            <label>Status</label>
                            <select class="form-control " name="is_active" required>
                                <option value="1">Aktiv</option>
                                <option value="0">Blok</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Price</label>
                            <input type="text" class="form-control" name="price">
                        </div>

                        <div class="form-group">
                            <label>Rasm yuklash</label>
                            <div id="imageDropzone" class="dropzone"></div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </form>
    </div>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

    <script>
        Dropzone.autoDiscover = false;

        const dropzone = new Dropzone("#imageDropzone", {
            url: "{{ route('admin.image.upload') }}",
            method: "post",
            paramName: "file",
            maxFiles: 1,
            maxFilesize: 2, // MB
            acceptedFiles: "image/*",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            dictDefaultMessage: "Rasm(ni) shu yerga tortib tashlang yoki bosing...",
            addRemoveLinks: true,
            dictRemoveFile: "❌",

            success: function (file, response) {
                console.log("✅ Yuklandi:", response);

                // Eski inputni o‘chir
                document.querySelectorAll('#mealForm input[name="image"]').forEach(el => el.remove());

                // Yangi yashirin input qo‘sh
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "image";
                hiddenInput.value = response.filename;
                document.querySelector("#mealForm").appendChild(hiddenInput);
            },

            removedfile: function (file) {
                file.previewElement.remove(); // Dropzonedan rasmni DOMdan o‘chiradi
                document.querySelectorAll('#mealForm input[name="image"]').forEach(el => el.remove());
                console.log("🗑 O‘chirildi:", file.name);
            }
        });
    </script>


@endsection



