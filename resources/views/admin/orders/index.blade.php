@php
    $colors = ['#4B4DFF', '#00A452', '#E40089', '#ED0000'];
     $planByMethod = ['karta' => 0, 'naqt' => 0];
    $factByMethod = ['karta' => 0, 'naqt' => 0];

    $oylikPlan = 0;
    $oylikFact = 0;

    foreach ($latestOrders as $order) {
        if (isset($order->customer) && $order->customer->type === 'oylik') {
            $oylikPlan += $order->total_amount;
            $oylikFact += $order->received_amount;
        } else {
            if ($order->payment_method === 'karta') {
                $planByMethod['karta'] += $order->total_amount;
                $factByMethod['karta'] += $order->received_amount;
            } elseif ($order->payment_method === 'naqt') {
                $planByMethod['naqt'] += $order->total_amount;
                $factByMethod['naqt'] += $order->received_amount;
            }
        }
    }
@endphp


@extends('layouts.admin')
@section('title')
    Customers
@endsection
@section('css')
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
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

        .card-header{
            border: 2px solid #DCDCE7 !important;
            border-bottom: none !important;
        }

        .customTable thead tr{
            background-color: #fff !important;

        }

        .customTable thead tr th{
            border: 2px solid #DCDCE7 !important;
            background-color: #fff !important;

        }

        .customTable thead tr .sortTable{
            font-size: 12px;
            font-weight: 400;
            color: #1C1C29;
        }

        .customTable tbody tr td{
            border: 2px solid #DCDCE7 !important;
        }

        .customTable tbody tr .sortTable{
            font-size: 12px;
            font-weight: 400;
            color: #1C1C29;

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
                <div style="display: flex; justify-content: space-between; padding: 15px 25px">
                    <div>
                        @foreach($meals as $index => $meal)
                            <div style="color: {{ $colors[$index % count($colors)] }};">
                                {{ $meal->name }}
                                ({{ $meal->total_count }} ta)
                            </div>
                        @endforeach
                    </div>
                    @if($dailyMeals->isNotEmpty())
                    <div>
                        <h6 class="fw-bold">Driver:</h6>
                        @php
                            $driverSums = [];
                        @endphp

                        @foreach($latestOrders as $order)
                            @if ($order->customer && $order->customer->type === 'odiy' && $order->payment_method === 'naqt')
                                @php
                                    $customerId = $order->driver->id;
                                    if (!isset($driverSums[$customerId])) {
                                        $driverSums[$customerId] = [
                                            'name' => $order->driver->name,
                                            'received' => 0,
                                        ];
                                    }
                                    $driverSums[$customerId]['received'] += $order->total_amount;
                                @endphp
                            @endif
                        @endforeach

                        @foreach($driverSums as $driver)
                            <div>{{ $driver['name'] }}: {{ number_format($driver['received'], 0, '.', ' ') }} so‘m</div>
                        @endforeach
                    </div>
                    <div>
                        <div><strong>Plan:</strong>
                            Karta: {{ number_format($planByMethod['karta'], 0, '.', ' ') }} |
                            Naqt: {{ number_format($planByMethod['naqt'], 0, '.', ' ') }}
                        </div>
                        <div><strong>Fakt:</strong>
                            Karta: {{ number_format($factByMethod['karta'], 0, '.', ' ') }} |
                            Naqt: {{ number_format($factByMethod['naqt'], 0, '.', ' ') }}
                        </div>
                        <div class="mt-3"><strong>Oylik mijozlar umumiy:</strong></div>
                        <div><strong></strong> {{ number_format($oylikPlan, 0, '.', ' ') }} so‘m</div>
{{--                        <div><strong>Fakt:</strong> {{ number_format($oylikFact, 0, '.', ' ') }} so‘m</div>--}}
                    </div>
                    @endif
                </div>

                @if($dailyMeals->isNotEmpty())
                <div class="card-header">
                <form method="POST" action="{{ route('admin.orders.store') }}">
                    @csrf
                    <input type="hidden" name="order_date" value="{{ request('order_date', now()->format('Y-m-d')) }}">
                    <div class="table-responsive mt-3" >
                        <table class="table table-bordered customTable">
                            <thead>
                            <tr>
                                <th style="width: 47px" class="sortTable"><span style="margin-left: 8px">ID</span></th>
                                <th>Mijoz</th>
                                <th style="background: #F5F5F7 !important;">Balance</th>
                                <th style="width: 50px; background: #F5F5F7 !important"><img style="margin-left: 3px" src="{{asset('/img/call-hospital.svg')}}"></th>
                                @foreach($meals as $index => $meal)
                                    <th style="color: {{ $colors[$index % count($colors)] }};">
                                        {{ $meal->name }}
                                    </th>
                                @endforeach
                                <th style="background: #F5F5F7 !important; width: 40px"><span style="margin-left: 3px">T</span></th>
                                <th>Cola</th>
                                <th>Dostavka</th>
                                <th>Kuryer</th>
                                <th>To‘lov</th>
                                <th style="background: #F5F5F7 !important;">Jami</th>
                            </tr>
                            </thead>
                            <tbody>
                            @for ($i = 0; $i < 1; $i++)
                                <tr>

                                    <td><span class="row-index">{{ $i + 1 }}</span></td>
                                    <td>
                                        <select  style="border: none; outline: none" name="orders[{{ $i }}][customer_id]" class="form-control customer-select select2" required>
                                            <option value="">Tanlang</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                        data-phone="{{ $customer->phone }}"
                                                        data-balance="{{ number_format($customer->balance, 3, '.', ' ') }}"
                                                    {{ old("orders.$i.customer_id") == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td style="background: #F5F5F7 !important;">
                                        <input style="background: #F5F5F7 !important; border: none" type="text" name="orders[{{ $i }}][balance]" class="form-control customer-balance" readonly value="{{ old("orders.$i.balance") }}">
                                    </td>
                                    <td>
                                        <input style="background: #fff !important; border: none" type="text"
                                               name="orders[{{ $i }}][phone]"
                                               class="form-control customer-phone copy-phone"
                                               readonly
                                               value="{{ old("orders.$i.phone") }}"
                                               onclick="copyToClipboard(this)">
                                    </td>

                                    @foreach($meals as $meal)
                                        <td>
                                            <input style="background: #fff !important; border: none" type="number"
                                                   name="orders[{{ $i }}][meals][{{ $meal->id }}]"
                                                   class="form-control meal-input"
                                                   data-price="{{ number_format($meal->price, 3, '.', ' ') }}"
                                                   min="0"
                                                   value="{{ old("orders.$i.meals.$meal->id", 0) }}">
                                        </td>
                                    @endforeach

                                    <td style="background: #F5F5F7 !important;"><input style="background: #F5F5F7 !important; border: none" type="total_meals" class="form-control total-meals" readonly value="{{ old("orders.$i.total_meals") }}"></td>
                                    <td><input style="background: #fff !important; border: none" type="number" name="orders[{{ $i }}][cola]" class="form-control cola-input" data-price="15000" value="{{ old("orders.$i.cola", 0) }}"></td>
                                    <td><input style="background: #fff !important; border: none" type="number" name="orders[{{ $i }}][delivery]" class="form-control delivery-input editable-delivery" value="{{ old("orders.$i.delivery", 20000) }}"></td>
                                    <td>
                                        <select style="border: none; outline: none" name="orders[{{ $i }}][driver_id]" class="form-control driver-select select2">
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}" {{ old("orders.$i.driver_id") == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select style="border: none; outline: none" name="orders[{{ $i }}][payment_type]" class="form-control payment-type">
                                            <option value="naqt" {{ old("orders.$i.payment_type") == 'naqt' ? 'selected' : '' }}>Naqd</option>
                                            <option value="karta" {{ old("orders.$i.payment_type") == 'karta' ? 'selected' : '' }}>Karta</option>
                                            <option value="transfer" {{ old("orders.$i.payment_type") == 'transfer' ? 'selected' : '' }}>Bank orqali</option>
                                        </select>
                                    </td>
                                    <td style="background: #F5F5F7 !important;"><input style="background: #F5F5F7 !important; border: none" type="text" class="form-control total-sum" readonly value="{{ old("orders.$i.total_sum") }}"></td>
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success mt-3">Buyurtmalarni Saqlash</button>
                </form>
            </div>
                @else
                    <div class="alert alert-warning">
                        Ovqat mavjud emas
                            <a href="{{ route('admin.daily_meal.create') }}" class="btn btn-primary">Qo'shish </a>

                    </div>
                @endif
        </div>
    </div>
    <div class="col-19 col-md-19 col-lg-19">

{{--        <form method="GET" class="form-inline mt-5 mb-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for="order_date_search" class="mr-2">Sana bo‘yicha qidirish:</label>--}}
{{--                <input type="date" id="order_date_search" name="order_date" class="form-control mr-2" value="{{ request('order_date', now()->format('Y-m-d')) }}">--}}
{{--                <button type="submit" class="btn btn-primary">Qidirish</button>--}}
{{--            </div>--}}
{{--        </form>--}}

    </div>
    @if(isset($latestOrders) && count($latestOrders) > 0)
            <div class="card mt-2">
                <div class="card-header">
                    <h5>So‘nggi Buyurtmalar</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 customTable">
                        <thead>
                        <tr>
                            <th style="width: 47px" class="sortTable"><span style="margin-left: 8px">ID</span></th>
                            <th>№</th>
                            <th>Mijoz</th>
                            <th style="width: 50px; background: #F5F5F7 !important"><img style="margin-left: 3px" src="{{asset('/img/call-hospital.svg')}}"></th>
                            <th style="background: #F5F5F7 !important;">Balance</th>
                            @php
                                $colors = ['#4B4DFF', '#00A452', '#E40089', '#ED0000'];
                            @endphp

                            @foreach($meals as $index => $meal)
                                <th style="color: {{ $colors[$index % count($colors)] }};">{{ $meal->name }}</th>
                            @endforeach
                            <th style="background: #F5F5F7 !important; width: 40px"><span style="margin-left: 3px">T</span></th>
                            <th>Cola</th>
                            <th>Dostavka</th>
                            <th>Kuryer</th>
                            <th>To‘lov</th>
                            <th style="background: #F5F5F7 !important;">Jami</th>
                            <th>Olindi</th>
                            <th style="width: 56px"><img src="{{asset('/img/pencil.svg')}}"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($latestOrders as $order)
                            <tr>
                                <td class="sortTable"><span style="margin-left: 8px">{{ $order->id }}</span></td>
                                <td>{{ $order->daily_order_number }}</td>
                                <td>
                                    @if(isset($order->customer) && $order->customer->type === 'oylik')
                                        <span style="color: blue;">{{ $order->customer->name }}</span>
                                    @else
                                        {{ $order->customer->name ?? '-' }}
                                    @endif
                                </td>
                                <td style="background: #F5F5F7 !important" onclick="copyToClipboard('{{ $order->customer->phone }}')">
                                    @if ($order->customer && $order->customer->phone)
                                        <a href="tel:{{ $order->customer->phone }}" style="text-decoration: none; color: inherit; margin-left: 3px">
                                            <img src="{{asset('/img/eye.svg')}}"
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>


                                <td style="background: #F5F5F7 !important;" class="{{ $order->customer->balance < 0 ? 'text-danger' : '' }}">
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
                                <td style="background: #F5F5F7 !important;"><strong style="margin-left: 3px">{{ $totalMeals }}</strong></td>
                                <td>{{ $order->cola_quantity }}</td>
                                <td>{{ number_format($order->delivery_fee, 0, ',', ' ') }} </td>
                                <td>{{ $order->driver->name ?? '-' }}</td>
                                <td>
                                    {{ ucfirst($order->payment_method) }}
                                    @if ($order->payment_method === 'naqt')
                                        <img src="{{ asset('/img/zam-zam-cash.svg') }}" alt="Nax" width="20">
                                    @elseif ($order->payment_method === 'karta')
                                        <img src="{{ asset('/img/card.svg') }}" alt="Card" width="20">
                                    @endif
                                </td>
                                <td style="background: #F5F5F7 !important;"><strong>{{ number_format($order->total_amount, 0, ',', ' ') }} </strong></td>
                                <td>
                                    <div class="received-amount-wrapper">
                                        <!-- Display mode -->
                                        <strong class="received-amount-display" style="color: {{ $order->received_amount < $order->total_amount ? 'red' : 'green' }}">
                                            {{ number_format($order->received_amount, 0, ',', ' ') }} so‘m
                                        </strong>

                                        <!-- Edit mode -->
                                        <div class="received-amount-edit d-none">
                                            <input type="number" class="received-amount-input form-control" value="{{ $order->received_amount }}" style="width: 120px; display: inline-block;">
                                            <button class="btn btn-sm btn-success save-received-amount" data-order-id="{{ $order->id }}">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td class="edit-received-amount" style="width: 56px"><img src="{{asset('/img/pencil.svg')}}"></td>
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
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                console.log('Copied to clipboard: ' + text);
            }).catch(function(err) {
                console.error('Failed to copy text: ', err);
            });
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".edit-received-amount").forEach(function (editBtn) {
                editBtn.addEventListener("click", function () {
                    const wrapper = this.closest("tr").querySelector(".received-amount-wrapper");
                    const display = wrapper.querySelector(".received-amount-display");
                    const editDiv = wrapper.querySelector(".received-amount-edit");

                    display.classList.add("d-none");
                    editDiv.classList.remove("d-none");
                });
            });

            document.querySelectorAll(".save-received-amount").forEach(function (saveBtn) {
                saveBtn.addEventListener("click", function () {
                    const orderId = this.getAttribute("data-order-id");
                    const wrapper = this.closest(".received-amount-wrapper");
                    const input = wrapper.querySelector(".received-amount-input");
                    const display = wrapper.querySelector(".received-amount-display");
                    const editDiv = wrapper.querySelector(".received-amount-edit");

                    const newValue = parseInt(input.value) || 0;

                    // AJAX orqali serverga yuborish
                    fetch(`/admin/orders/${orderId}/update-received-amount`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ received_amount: newValue })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                display.textContent = newValue.toLocaleString('uz-UZ') + " so‘m";
                                display.style.color = (newValue < data.total_amount) ? "red" : "green";

                                editDiv.classList.add("d-none");
                                display.classList.remove("d-none");
                            } else {
                                alert("Xatolik yuz berdi: " + data.message);
                            }
                        });
                });
            });
        });
    </script>



@endsection
