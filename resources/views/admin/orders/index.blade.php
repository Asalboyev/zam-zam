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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .customTable {
            border-collapse: collapse;
            width: 100%;
            table-layout: auto; /* Ustun kengligi avtomatik bo‘lsin */
        }

        .customTable th,
        .customTable td {
            border: 2px solid #DCDCE7 !important;
            padding: 6px 8px;
            text-align: center;
            vertical-align: middle;
            white-space: normal;     /* Matnni qatordan qatorga o‘tkazish */
            word-break: break-word;  /* Uzoq so‘zlarni bo‘lib yozish */
        }

        /* Kichik ekranda skroll bo‘lishi uchun */
        .table-responsive {
            overflow-x: auto;
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
            @foreach($latestOrders as $i => $order)
                @if($errors->has("orders.$i.meals"))
                    <div class="alert alert-danger alert-dismissible show fade">
                        <div class="alert-body">
                            <button type="button" class="close" data-dismiss="alert">
                                <span>×</span>
                            </button>
                            {{ $errors->first("orders.$i.meals") }}
                        </div>
                    </div>
                @endif
            @endforeach

            <div style="display: flex; justify-content: space-between; padding: 15px 25px">
                @if($dailyMeals->isNotEmpty())
                <div>
                    @foreach($meals as $index => $meal)
                        <div style="color: {{ $colors[$index % count($colors)] }};">
                            {{ $meal->name }}
                            ({{ $meal->total_count }} ta)
                        </div>
                    @endforeach
                </div>
                @endif
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
                <div class="d-flex align-items-center gap-3">
                    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-3">
                        <label for="order_date" class="fw-bold">Sanani tanlang:</label>
                        <input type="date" name="order_date" id="order_date" class="form-control w-auto d-inline-block"
                               value="{{ $date }}"
                               onchange="this.form.submit()">
                    </form>


                </div>
            @if($dailyMeals->isNotEmpty())
                <div class="card-header">
                    @for ($i = 0; $i < 1; $i++)
                        <form method="POST" action="{{ route('admin.orders.store') }}">
                            @csrf
                            <input type="hidden"  name="orders[{{ $i }}][order_date]" value="{{ $date }}">
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered customTable">
                                    <thead>
                                    <tr>
                                        <th style="width: 47px" class="sortTable"><span style=" opacity: 0.4"
                                                                                        class="sortTable"><span
                                                    style="margin-left: 8px">ID</span></th>
                                        <th style="width: 200px">Mijoz</th>
                                        <th style="background: #F5F5F7 !important; width: 200px">Balance</th>
                                        <th style="max-width: 50px !important; background: #F5F5F7 !important"><img
                                                style="margin-left: 3px" src="{{asset('/img/call-hospital.svg')}}"></th>
                                        @foreach($meals as $index => $meal)
                                            <th style="width: 200px; color: {{ $colors[$index % count($colors)] }};">
                                                {{ $meal->name }}
                                            </th>
                                        @endforeach
                                        <th style="background: #F5F5F7 !important; width: 40px"><span
                                                style="margin-left: 3px">T</span></th>
                                        <th style="width: 200px">Cola</th>
                                        <th style="width: 250px">Dostavka</th>
                                        <th style="width: 250px">Kuryer</th>
                                        <th style="width: 300px">To‘lov</th>
                                        <th style="width: 400px; background: #F5F5F7 !important;">Jami</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <tr>

                                        <td style="width: 47px" class="sortTable"><span
                                                style="margin-left: 8px; opacity: 0.4"><span
                                                    class="row-index">{{ $i + 1 }}</span></td>
                                        <td>
                                            <select name="orders[{{ $i }}][customer_id]"
                                                    class="form-control customer-select select2"
                                                    data-index="{{ $i }}"
                                                    required>
                                                <option value="">Tanlang</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}"
                                                            data-phone="{{ $customer->phone }}"
                                                            data-balance="{{ number_format($customer->balance, 0, '.', ' ') }}"
                                                            data-last-driver="{{ $customer->lastOrder->driver_id ?? '' }}"
                                                        {{ old("orders.$i.customer_id") == $customer->id ? 'selected' : '' }}>
                                                        {{ $customer->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <a href="{{ route('admin.customers.create') }}" >Yaratish</a>
                                        </td>
                                        <td style="background: #F5F5F7 !important;">
                                            <input style="background: #F5F5F7 !important; border: none" type="text"
                                                   name="orders[{{ $i }}][balance]"
                                                   class="form-control customer-balance" readonly
                                                   value="{{ old("orders.$i.balance") }}">
                                        </td>
                                        <td style="background: #F5F5F7">
                                            <img
                                                style="margin-left: 3px" src="{{asset('/img/call-hospital.svg')}}">
                                        </td>
                                        @foreach($meals as $meal)
                                            <td>
                                                <input
                                                    style="background: #fff !important; border: none"
                                                    type="number"
                                                    name="orders[{{ $i }}][meals][{{ $meal->id }}]"
                                                    class="form-control meal-input @error("orders.$i.meals") is-invalid @enderror"
                                                    data-price="{{ number_format($meal->price, 3, '.', ' ') }}"
                                                    min="0"
                                                    step="1"
                                                    value="{{ old("orders.$i.meals.$meal->id", 0) }}"
                                                    oninput="this.value = this.value.replace(/^0+(?=\d)/,'').replace(/[^0-9]/g,'')"
                                                >
                                            </td>
                                        @endforeach
                                        <td style="background: #F5F5F7 !important;"><input
                                                style="background: #F5F5F7 !important; border: none; width: 20px"
                                                type="total_meals" class="total-meals" readonly
                                                value="{{ old("orders.$i.total_meals") }}"></td>
                                        <td><input style="background: #fff !important; border: none" type="number"
                                                   name="orders[{{ $i }}][cola]" class="form-control cola-input"
                                                   data-price="15000" value="{{ old("orders.$i.cola", 0) }}">
                                        </td>
                                        <td><input style="background: #fff !important; border: none" type="number"
                                                   name="orders[{{ $i }}][delivery]"
                                                   class="form-control delivery-input editable-delivery"
                                                   value="{{ old("orders.$i.delivery", 20000) }}"></td>
                                        <td>
                                            <select name="orders[{{ $i }}][driver_id]"
                                                    class="form-control driver-select select2"
                                                    data-index="{{ $i }}"
                                                    required>
                                                <option value="">Tanlang</option>
                                                @foreach($drivers as $driver)
                                                    <option value="{{ $driver->id }}"
                                                        {{ old("orders.$i.driver_id") == $driver->id ? 'selected' : '' }}>
                                                        {{ $driver->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select style="border: none; outline: none"
                                                    name="orders[{{ $i }}][payment_type]"
                                                    class="form-control payment-type">
                                                <option
                                                    value="naqt" {{ old("orders.$i.payment_type") == 'naqt' ? 'selected' : '' }}>
                                                    Naqd
                                                </option>
                                                <option
                                                    value="karta" {{ old("orders.$i.payment_type") == 'karta' ? 'selected' : '' }}>
                                                    Karta
                                                </option>
                                                <option
                                                    value="transfer" {{ old("orders.$i.payment_type") == 'transfer' ? 'selected' : '' }}>
                                                    Bank orqali
                                                </option>
                                            </select>
                                        </td>
                                        <td style="background: #F5F5F7 !important;"><input
                                                style="background: #F5F5F7 !important; border: none" type="text"
                                                class="form-control total-sum" readonly
                                                value="{{ old("orders.$i.total_sum") }}"></td>
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
                        <th style="width: 47px" class="sortTable"><span style="margin-left: 8px; opacity: 0.4">ID</span>
                        </th>
                        <th style="width: 50px">№</th>
                        <th style="width: 100px">Mijoz</th>
                        <th style="width: 50px; background: #F5F5F7 !important"><img style="margin-left: 3px" src="{{asset('/img/call-hospital.svg')}}">
                        </th>
                        <th style="background: #F5F5F7 !important; width: 100px">Balance</th>
                        @php
                            $colors = ['#4B4DFF', '#00A452', '#E40089', '#ED0000'];
                        @endphp

                        @foreach($meals as $index => $meal)
                            <th style=" width: 100px;color: {{ $colors[$index % count($colors)] }};">{{ $meal->name }}</th>
                        @endforeach
                        <th style="background: #F5F5F7 !important; width: 40px"><span style="margin-left: 3px">T</span>
                        </th>
                        <th style="width: 100px">Cola</th>
                        <th style="width: 100px">Dostavka</th>
                        <th style="width: 100px">Kuryer</th>
                        <th style="width: 100px">To‘lov</th>
                        <th style="background: #F5F5F7 !important; width: 100px">Jami</th>
                        <th style="width: 50px">Olindi</th>
                        <th></th>
                        <th style="width: 56px"><img src="{{asset('/img/pencil.svg')}}"></th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach($latestOrders as $order)
                        <tr>
                            <td class="sortTable"><span style="margin-left: 8px; opacity: 0.4">{{ $order->id }}</span>
                            </td>
                            <td>{{ $order->daily_order_number }}</td>
                            <td>
                                @if(isset($order->customer) && $order->customer->type === 'oylik')
                                    <span style="color: blue;">{{ $order->customer->name }}</span>
                                @else
                                    {{ $order->customer->name ?? '-' }}
                                @endif
                            </td>
                            <td style="background: #F5F5F7 !important; position: relative;"
                                onclick="showPhoneTooltip(this, '{{ $order->customer->phone ?? '' }}')">
                                @if ($order->customer && $order->customer->phone)
                                    <img src="{{ asset('/img/call-hospital.svg') }}" alt="Call"
                                         style="cursor: pointer;">
                                @else
                                    -
                                @endif
                            </td>
                            <td style="background: #F5F5F7 !important;"
                                class="{{ $order->customer->balance < 0 ? 'text-danger' : '' }}">
                                {{ number_format($order->customer->balance, 0, '.', ' ') }}
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
                            <td style="background: #F5F5F7 !important;"><strong
                                    style="margin-left: 3px">{{ $totalMeals }}</strong></td>
                            <td>{{ $order->cola_quantity }}</td>
                            <td>{{ number_format($order->delivery_fee, 0, ',', ' ') }} </td>
                            <td>{{ $order->driver->name ?? '-' }}</td>
{{--                            <td>--}}
{{--                                {{ ucfirst($order->payment_method) }}--}}
{{--                                @if ($order->payment_method === 'naqt')--}}
{{--                                    <img src="{{ asset('/img/zam-zam-cash.svg') }}" alt="Nax" width="20">--}}
{{--                                @elseif ($order->payment_method === 'karta')--}}
{{--                                    <img src="{{ asset('/img/card.svg') }}" alt="Card" width="20">--}}
{{--                                @endif--}}
{{--                            </td>--}}
                            <td>
                                <select class="payment-method-select form-control"
                                        data-order-id="{{ $order->id }}"
                                        style="width: 140px; display: inline-block; background-position: right 8px center; background-repeat: no-repeat; background-size: 20px;">
                                    <option value="naqt" data-icon="{{ asset('/img/zam-zam-cash.svg') }}" {{ $order->payment_method === 'naqt' ? 'selected' : '' }}>
                                        Naqt
                                    </option>
                                    <option value="karta" data-icon="{{ asset('/img/card.svg') }}" {{ $order->payment_method === 'karta' ? 'selected' : '' }}>
                                        Karta
                                    </option>
                                </select>
                            </td>

                            <td style="background: #F5F5F7 !important;">
                                <strong>{{ number_format($order->total_amount, 0, ',', ' ') }}</strong>
                            </td>
                            <td>
                                <div class="received-amount-wrapper">
                                    @php
                                        $bgColor = '';
                                        if (strtolower($order->customer->type) === 'oylik') {
                                            $bgColor = 'color: blue;';
                                        } elseif ($order->received_amount < $order->total_amount) {
                                            $bgColor = 'color: red;';
                                        } elseif ($order->received_amount == $order->total_amount) {
                                            $bgColor = 'color: green;';
                                        }
                                    @endphp

                                    <input
                                        type="text"
                                        class="received-amount-input form-control"
                                        value="{{ number_format($order->received_amount, 0, '.', ' ') }}"
                                        style="width: 120px; display: inline-block; {{ $bgColor }}"
                                        {{ strtolower($order->customer->type) === 'oylik' ? 'disabled' : '' }}
                                        data-order-id="{{ $order->id }}"
                                    >


                                </div>
                            </td>


                            <td style="width: 56px">
                                <button
                                    class="btn btn-sm btn-success save-received-amount"
                                    data-order-id="{{ $order->id }}"
                                    {{ strtolower($order->customer->type) === 'oylik' ? 'disabled' : '' }}>
                                    <i class="fa fa-check"></i>
                                </button>
                            </td>
                            <td class="edit-received-amount" style="width: 56px">
                                <a href="{{route('admin.orders.edit',$order->id)}}"><img
                                        src="{{asset('/img/pencil.svg')}}" style="cursor: pointer;"></a>
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
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const selects = document.querySelectorAll(".payment-method-select");

                    function updateSelectIcon(select) {
                        const selectedOption = select.options[select.selectedIndex];
                        const iconUrl = selectedOption.dataset.icon;
                        select.style.backgroundImage = `url(${iconUrl})`;
                    }

                    selects.forEach(select => {
                        updateSelectIcon(select); // yuklanganda ham icon chiqadi

                        select.addEventListener("change", function () {
                            const orderId = this.dataset.orderId;
                            const newMethod = this.value;
                            updateSelectIcon(this); // icon yangilash

                            fetch(`/admin/orders/${orderId}/update-payment-method`, {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({payment_method: newMethod})
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (!data.success) {
                                        console.warn("Xatolik:", data.message || "Ma'lumot saqlanmadi.");
                                    }
                                })
                                .catch(err => {
                                    console.error("Server bilan ulanishda xatolik:", err);
                                });
                        });
                    });
                });
            </script>

            <script>
                $(document).on('change', '.customer-select', function () {
                    let lastDriverId = $(this).find(':selected').data('last-driver');
                    let index = $(this).data('index');
                    let driverSelect = $(`select[name="orders[${index}][driver_id]"]`);

                    if (lastDriverId) {
                        driverSelect.val(lastDriverId).trigger('change'); // avtomatik tanlash
                    } else {
                        driverSelect.val('').trigger('change'); // bo‘sh qoldirish
                    }
                });

                document.addEventListener("DOMContentLoaded", function () {
                    document.querySelectorAll('.customer-select').forEach(function (selectEl) {
                        selectEl.addEventListener('change', function () {
                            const lastDriverId = this.options[this.selectedIndex].dataset.lastDriver;
                            const row = this.closest('tr');
                            const driverSelect = row.querySelector('.driver-select');

                            if (lastDriverId) {
                                driverSelect.value = lastDriverId;
                            } else {
                                driverSelect.value = ""; // oldingi tanlovni tozalash
                            }

                            // Agar Select2 ishlatayotgan bo‘lsangiz
                            if ($(driverSelect).hasClass('select2')) {
                                $(driverSelect).trigger('change');
                            }
                        });
                    });
                });
            </script>

            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

            <script>
                // Select2-ni ishga tushirish
                $('.select2').select2();

                // Matndan raqamga aylantirish
                function parseNumber(value) {
                    if (!value) return 0;
                    return parseFloat(value.toString().replace(/[\s,]/g, '')) || 0;
                }

                // Har bir jadval qatori bo‘yicha hisob-kitob
                function updateRowCalculations(row) {
                    let totalMeals = 0;
                    let totalSum = 0;

                    // 🍽 Ovqatlar hisoblash
                    row.querySelectorAll(".meal-input").forEach(input => {
                        const count = parseNumber(input.value);
                        const price = parseNumber(input.dataset.price);

                        if (count > 0) { // faqat 0 dan katta bo‘lsa qo‘shamiz
                            totalMeals += count;
                            totalSum += count * price;
                        }
                    });

                    // 🥤 Cola hisoblash (bonus qo‘shish)
                    const colaInput = row.querySelector(".cola-input");
                    if (colaInput) {
                        let colaCount = parseNumber(colaInput.value);
                        const colaPrice = parseNumber(colaInput.dataset.price);

                        // Bonus colalar (8+ ta ovqat → 1, 16+ ta ovqat → 2)
                        let bonusCola = 0;
                        if (totalMeals >= 16) {
                            bonusCola = 2;
                        } else if (totalMeals >= 8) {
                            bonusCola = 1;
                        }

                        // Agar foydalanuvchi qo‘lda o‘zgartirmagan bo‘lsa → bonus qo‘shiladi
                        if (!colaInput.classList.contains('manual-edit')) {
                            colaCount = bonusCola;
                            colaInput.value = colaCount;
                        }

                        // Bonus colalar narxini hisobga olmaslik
                        if (colaCount > bonusCola) {
                            totalSum += (colaCount - bonusCola) * colaPrice;
                        }
                    }

                    // 🚚 Yetkazib berish narxi
                    const deliveryInput = row.querySelector(".delivery-input");
                    if (deliveryInput && !deliveryInput.classList.contains('manual-edit')) {
                        deliveryInput.value = totalMeals > 8 ? 0 : 20000;
                    }
                    totalSum += parseNumber(deliveryInput?.value || 0);

                    // 📊 Natijalarni chiqarish
                    row.querySelector(".total-meals").value = totalMeals;
                    row.querySelector(".total-sum").value = totalSum.toLocaleString('ru-RU');
                }

                // Barcha qatorlarni hisoblash
                function recalculateAllRows() {
                    document.querySelectorAll("tbody tr").forEach(row => {
                        updateRowCalculations(row);
                    });
                }

                // 🔄 Input o‘zgarganda avtomatik hisoblash
                document.addEventListener("input", function (e) {
                    // Agar foydalanuvchi colani qo‘lda tahrir qilsa → manual-edit belgilanadi
                    if (e.target.classList.contains('cola-input')) {
                        e.target.classList.add('manual-edit');
                    }
                    recalculateAllRows();
                });

                // 👤 Mijoz tanlanganda ma'lumotlarni yuklash
                $(document).on('change', '.customer-select', function () {
                    const row = $(this).closest('tr');
                    const selectedOption = $(this).find('option:selected');
                    const phone = selectedOption.data('phone');
                    const balance = selectedOption.data('balance');

                    row.find('.customer-phone').val(phone || '');
                    row.find('.customer-balance').val(balance || '');

                    updateRowCalculations(row[0]);
                });

                // ✏️ Yetkazib berish qiymati qo‘lda tahrir qilinganda
                document.querySelectorAll('.editable-delivery').forEach(input => {
                    input.addEventListener('input', function () {
                        input.classList.add('manual-edit');
                        const row = input.closest('tr');
                        updateRowCalculations(row);
                    });
                });

                // 🚀 Dastlabki hisob-kitob
                document.addEventListener("DOMContentLoaded", function () {
                    recalculateAllRows();
                });
            </script>


            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const rows = document.querySelectorAll("tr");

                    rows.forEach(row => {
                        const saveBtn = row.querySelector(".save-received-amount");
                        const input = row.querySelector(".received-amount-input");
                        const displayAmount = row.querySelector(".received-amount-display");

                        if (!saveBtn || !input) return;

                        saveBtn.addEventListener("click", function () {
                            if (input.disabled || saveBtn.disabled) return;

                            const orderId = this.dataset.orderId;
                            const newAmount = input.value;

                            fetch(`/admin/orders/${orderId}/update-received-amount`, {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({ received_amount: newAmount })
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        // Sahifani yangilash
                                        location.reload();
                                    } else {
                                        console.warn("Xatolik:", data.message || "Ma'lumot saqlanmadi.");
                                    }
                                })
                                .catch(err => {
                                    console.error("Server bilan ulanishda xatolik:", err);
                                });
                        });
                    });
                });
            </script>
            <script>
                function showPhoneTooltip(tdElement, phoneNumber) {
                    // Avvalgi tooltipni tozalash
                    const existingTooltip = tdElement.querySelector('.phone-tooltip');
                    if (existingTooltip) {
                        existingTooltip.remove();
                    }

                    if (!phoneNumber) return;

                    // Tooltip yaratish
                    const tooltip = document.createElement('div');
                    tooltip.className = 'phone-tooltip';
                    tooltip.textContent = phoneNumber;

                    // Tooltipni style qilish
                    tooltip.style.position = 'absolute';
                    tooltip.style.top = '50%';
                    tooltip.style.width = '160px';
                    tooltip.style.left = '50%';
                    tooltip.style.transform = 'translate(-50%, -50%)';
                    tooltip.style.backgroundColor = '#fff';
                    tooltip.style.border = '1px solid #ccc';
                    tooltip.style.padding = '6px 10px';
                    tooltip.style.borderRadius = '5px';
                    tooltip.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.2)';
                    tooltip.style.zIndex = '1000';

                    tdElement.appendChild(tooltip);

                    // 15 soniyadan keyin yo'q qilish
                    setTimeout(() => {
                        tooltip.remove();
                    }, 5000);
                }
            </script>
        @endsection
