@php
    $colors = ['#4B4DFF', '#00A452', '#E40089', '#ED0000'];
@endphp
@extends('layouts.admin')
@section('title')
    Customers
@endsection
@section('css')
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .responsive-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 3rem; /* O'rtadagi masofa */
            margin-left: auto;
            margin-right: auto;
        }

        .responsive-column {
            flex: 1 1 300px;
        }

        @media screen and (max-width: 768px) {
            .responsive-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
                margin-left: 1rem;
                margin-right: 1rem;
            }

            .responsive-column {
                width: 100%;
            }
        }

    </style>
@endsection
@section('content')

    <div class="col-19 col-md-19 col-lg-19">

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
                <form method="POST" action="{{ route('admin.orders.update', $order->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="d-flex align-items-center gap-3">
                        <label for="order_date" class="fw-bold">Buyurtma tahrirlash</label>
                        <input type="date" name="order_date" id="order_date"
                               class="form-control w-auto"
                               value="{{ old('order_date', $order->order_date->format('Y-m-d')) }}">
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered customTable">
                            <thead>
                            <tr>
                                <th>Mijoz</th>
                                @foreach($meals as $meal)
                                    <th>{{ $meal->name }}</th>
                                @endforeach
                                <th>Totla</th>
                                <th>Cola</th>
                                <th>Dostavka</th>
                                <th>Kuryer</th>
                                <th>To‘lov</th>
                                <th>Jami</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                {{-- Mijoz tanlash --}}
                                <td>
                                    <select name="customer_id" class="form-control" required>
                                        <option value="">Tanlang</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ old('customer_id', $order->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                @php
                                    $totalQuantity = 0;
                                @endphp

                                @foreach($meals as $index => $meal)
                                    @php
                                        $quantity = old("meals.$meal->id", $order->{'meal_'.($index+1).'_quantity'});
                                        $totalQuantity += (int) $quantity;
                                    @endphp
                                    <td>
                                        <input type="number"
                                               name="meals[{{ $meal->id }}]"
                                               class="form-control"
                                               min="0"
                                               value="{{ $quantity }}">
                                    </td>
                                @endforeach

                                <td>
                                    <strong>{{ $totalQuantity }}</strong>
                                </td>

                                <td>
                                    <input type="number" name="cola" class="form-control"
                                           value="{{ old('cola', $order->cola_quantity) }}">
                                </td>

                                {{-- Delivery --}}
                                <td>
                                    <input type="number" name="delivery" class="form-control"
                                           value="{{ old('delivery', $order->delivery_fee) }}">
                                </td>

                                {{-- Driver --}}
                                <td>
                                    <select name="driver_id" class="form-control">
                                        <option value="">Tanlang</option>
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver->id }}"
                                                {{ old('driver_id', $order->driver_id) == $driver->id ? 'selected' : '' }}>
                                                {{ $driver->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- Payment type --}}

                                <td>
                                    <select name="payment_method" class="form-control">
                                        <option value="naqt" {{ old('payment_method', $order->payment_method) == 'naqt' ? 'selected' : '' }}>Naqd</option>
                                        <option value="karta" {{ old('payment_method', $order->payment_method) == 'karta' ? 'selected' : '' }}>Karta</option>
                                        <option value="transfer" {{ old('payment_method', $order->payment_method) == 'transfer' ? 'selected' : '' }}>Bank orqali</option>
                                    </select>
                                </td>


                                {{-- Jami readonly --}}
                                <td>
                                    <input type="text" class="form-control" readonly
                                           value="{{ number_format($order->total_amount, 0, '.', ' ') }}">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Yangilash</button>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $('.select2').select2();

        function parseNumber(value) {
            if (!value) return 0;
            // remove spaces and commas, convert to float
            return parseFloat(value.toString().replace(/[\s,]/g, '')) || 0;
        }

        function updateRowCalculations(row) {
            let totalMeals = 0;
            let totalSum = 0;

            // Hisob ovqatlar uchun
            row.querySelectorAll(".meal-input").forEach(input => {
                const count = parseNumber(input.value);
                const price = parseNumber(input.dataset.price);
                totalMeals += count;
                totalSum += count * price;
            });

            // Cola hisoblash
            const colaInput = row.querySelector(".cola-input");
            const colaCount = parseNumber(colaInput.value);
            const colaPrice = parseNumber(colaInput.dataset.price);
            totalSum += colaCount * colaPrice;

            // Yetkazib berish narxi
            const deliveryInput = row.querySelector(".delivery-input");
            if (!deliveryInput.classList.contains('manual-edit')) {
                deliveryInput.value = totalMeals > 8 ? 0 : 20000;
            }
            totalSum += parseNumber(deliveryInput.value);

            // Natijalarni chiqarish
            row.querySelector(".total-meals").value = totalMeals;

            // Tozalangan formatda ko‘rsatish (masalan: 275000 → 275 000)
            row.querySelector(".total-sum").value = totalSum.toLocaleString('ru-RU');
        }

        // Har bir inputda hisobni yangilash
        document.addEventListener("input", function () {
            document.querySelectorAll("tbody tr").forEach(function (row) {
                updateRowCalculations(row);
            });
        });

        // Select o‘zgarganda malumotlarni yuklash va hisoblash
        $(document).on('change', '.customer-select', function () {
            const row = $(this).closest('tr');
            const selectedOption = $(this).find('option:selected');
            const phone = selectedOption.data('phone');
            const balance = selectedOption.data('balance');

            row.find('.customer-phone').val(phone);
            row.find('.customer-balance').val(balance);

            updateRowCalculations(row[0]);
        });

        // Yetkazib berish qiymati qo‘lda tahrir qilinganda
        document.querySelectorAll('.editable-delivery').forEach(input => {
            input.addEventListener('input', function () {
                input.classList.add('manual-edit');
                const row = input.closest('tr');
                updateRowCalculations(row);
            });
        });
    </script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function () {
                console.log('Copied to clipboard: ' + text);
            }).catch(function (err) {
                console.error('Failed to copy text: ', err);
            });
        }
    </script>

@endsection
