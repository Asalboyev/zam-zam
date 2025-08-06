@php
    $colors = ['#4B4DFF', '#00A452', '#E40089', '#ED0000'];
@endphp
@extends('layouts.admin')
@section('title')
    Customers
@endsection
@section('css')
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endsection
@section('content')
    <div class="card p-3 mb-4 border border-primary rounded" style="display: flex; flex-direction: row; justify-content: space-between">
        <form method="GET" action="{{ route('admin.orders.all') }}">
            <div class="d-flex align-items-center gap-3">
                <label for="order_date" class="fw-bold">Buyurtma sanasi:</label>
                <input type="date" name="order_date" id="order_date" class="form-control w-auto"
                       value="{{ request('order_date', now()->format('Y-m-d')) }}">
                <button type="submit" class="btn btn-primary">Tanlash</button>
            </div>

        </form>
        <div class="row mt-1 " style="display: flex; gap: 60px; margin-right: 40px">
            <div>
                <h6 class="fw-bold">Mijozlar:</h6>
                @foreach($latestOrders as $customer)
                    <div>{{ $customer->customer->name }}: {{ number_format($customer['total_amount'], 0, '.', ' ') }} so'm</div>
                @endforeach

            </div>
{{--            <div>--}}
{{--                <h6 class="fw-bold">Haydovchilar:</h6>--}}
{{--                @foreach($latestOrders as $driver)--}}
{{--                    <div>{{ $driver->driver->name }}: {{ number_format($driver['received_amount'], 0, '.', ' ') }} so'm</div>--}}
{{--                @endforeach--}}
{{--            </div>--}}
            <div>
                <div><strong>Plan:</strong>
                    Karta: {{ number_format($planByMethod['karta'] ?? 0, 0, '.', ' ') }} |
                    Naqt: {{ number_format($planByMethod['naqt'] ?? 0, 0, '.', ' ') }}
                </div>
                <div><strong>Fakt:</strong>
                    Karta: {{ number_format($factByMethod['karta'] ?? 0, 0, '.', ' ') }} |
                    Naqt: {{ number_format($factByMethod['naqt'] ?? 0, 0, '.', ' ') }}
                </div>
            </div>

        </div>
    </div>
    <div class="col-19 col-md-19 col-lg-19">

        @if(isset($latestOrders) && count($latestOrders) > 0)
            <div class="card mt-2">
                <div class="card-header">
                    <h5> Buyurtmalar</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>#</th>
                            <th>Mijoz</th>
                            <th>Tel</th>
                            <th>Balance</th>
                            @foreach($meals as $meal)
                                <th>{{ $meal->name }}</th>
                            @endforeach
                            <th>T</th>
                            <th>Cola</th>
                            <th>Dostavka</th>
                            <th>Kuryer</th>
                            <th>To‘lov</th>
                            <th>Jami</th>
                            <th>Olingan</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($latestOrders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->daily_order_number }}</td>
                                <td>{{ $order->customer->name ?? '-' }}</td>
                                <td onclick="copyToClipboard(this)">
                                    {{ $order->customer->phone ?? '-' }}
                                </td>
                                <td class="{{ $order->customer->balance < 0 ? 'text-danger' : '' }}">
                                    {{ $order->customer->balance }}
                                </td>
                                @php $totalMeals = 0; @endphp
                                @foreach($meals as $meal)
                                    @php
                                        $mealQty = 0;
                                        if ($order->meal_1_id == $meal->id) $mealQty = $order->meal_1_quantity;
                                        if ($order->meal_2_id == $meal->id) $mealQty = $order->meal_2_quantity;
                                        if ($order->meal_3_id == $meal->id) $mealQty = $order->meal_3_quantity;
                                        if ($order->meal_4_id == $meal->id) $mealQty = $order->meal_4_quantity;
                                        $totalMeals += $mealQty;
                                    @endphp
                                    <td>{{ $mealQty > 0 ? $mealQty : '-' }}</td>
                                @endforeach
                                <td><strong>{{ $totalMeals }}</strong></td>
                                <td>{{ $order->cola_quantity }}</td>
                                <td>{{ number_format($order->delivery_fee, 0, ',', ' ') }} so‘m</td>
                                <td>{{ $order->driver->name ?? '-' }}</td>
                                <td>{{ ucfirst($order->payment_method) }}</td>
                                <td><strong>{{ number_format($order->total_amount, 0, ',', ' ') }} </strong></td>
                                <td><strong>{{ number_format($order->received_amount, 0, ',', ' ') }} </strong></td>
                                <td> <a href="{{ route('admin.orders.edit', $order->id) }}" class="icon-btn">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
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
        function copyToClipboard(element) {
            const text = element.innerText.trim();
            if (text === '-') return;

            // Temporary input yaratamiz
            const tempInput = document.createElement("input");
            tempInput.value = text;
            document.body.appendChild(tempInput);

            tempInput.select();
            tempInput.setSelectionRange(0, 99999); // mobile compatibility
            document.execCommand("copy");
            document.body.removeChild(tempInput);

            // Optional: qisqa feedback berish
            element.style.backgroundColor = "#d4edda"; // yashil fon
            setTimeout(() => {
                element.style.backgroundColor = ""; // eski holatga qaytarish
            }, 500);
        }
    </script>


@endsection
