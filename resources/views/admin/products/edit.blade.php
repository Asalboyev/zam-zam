@extends('layouts.admin')

@section('title')
    Maxsulot o'zgartirish
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
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

        .dz-preview:hover .dz-remove {
            display: block;
        }
        .dropzone .dz-preview .dz-image {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
        }
        .dropzone .dz-preview .dz-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
@endsection

@section('content')
    <div class="col-12 col-md-1 col-lg-12">
        <form action="{{ route('admin.products.update', $product->id) }}"
              id="mealForm"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h4>Maxsulot</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nomi</label>
                        <input type="text" name="name" class="form-control" required value="{{ $product->name }}">
                    </div>
                    <div class="form-group status-group">
                        <label>Status</label>
                        <select class="form-control" name="is_active" required>
                            <option value="1" {{ $product->is_active == 1 ? 'selected' : '' }}>Aktiv</option>
                            <option value="0" {{ $product->is_active == 0 ? 'selected' : '' }}>Blok</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input type="text" class="form-control" name="price" value="{{ $product->price }}">
                    </div>
                    <div class="form-group">
                        <label>Rasm yuklash</label>
                        <div id="imageDropzone" class="dropzone"></div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button class="btn btn-primary">Saqlash</button>
                </div>
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
            maxFilesize: 2,
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            dictDefaultMessage: "Rasm(ni) shu yerga tortib tashlang yoki bosing...",
            dictRemoveFile: "❌",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },

            init: function () {
                @if(!empty($product->image))
                let mockFile = {
                    name: "{{ $product->image }}",
                    size: 123456,
                    accepted: true
                };

                this.emit("addedfile", mockFile);
                this.emit("thumbnail", mockFile, "/uploads/{{ $product->image }}");
                this.emit("complete", mockFile);
                mockFile.previewElement.classList.add('dz-success', 'dz-complete');

                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "image";
                input.value = "{{ $product->image }}";
                document.querySelector("#mealForm").appendChild(input);
                @endif

                    this.on("success", function (file, response) {
                    document.querySelectorAll('#mealForm input[name="image"]').forEach(el => el.remove());

                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "image";
                    input.value = response.filename;
                    document.querySelector("#mealForm").appendChild(input);
                });

                this.on("removedfile", function (file) {
                    document.querySelectorAll('#mealForm input[name="image"]').forEach(el => el.remove());
                });
            }
        });
    </script>
@endsection
