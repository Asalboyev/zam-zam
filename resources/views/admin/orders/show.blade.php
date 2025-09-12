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

        .card-header {
            border: 2px solid #DCDCE7 !important;
            border-bottom: none !important;
        }

        .customTable thead tr {
            background-color: #fff !important;

        }

        .customTable thead tr th {
            border: 2px solid #DCDCE7 !important;
            background-color: #fff !important;

        }

        .customTable thead tr .sortTable {
            font-size: 12px;
            font-weight: 400;
            color: #1C1C29;
        }

        .customTable tbody tr td {
            border: 2px solid #DCDCE7 !important;
        }

        .customTable tbody tr .sortTable {
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
    <div class="card p-3 mb-4 border border-primary rounded"
         style="display: flex; flex-direction: row; justify-content: space-between">
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
            </div>
            <div>
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
//                            $driverSums[$customerId]['received'] += $order->received_amount;
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
                {{--                <div><strong>Fakt:</strong> {{ number_format($oylikFact, 0, '.', ' ') }} so‘m</div>--}}

            </div>

        </div>
    </div>
    <div class="col-19 col-md-19 col-lg-19">

        @if(isset($latestOrders) && count($latestOrders) > 0)
            <div class="card mt-2">
                <div class="card-header">
                    <h5>Buyurtmalar </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 customTable">
                        <thead>
                        <tr>
                            <th style="width: 47px" class="sortTable"><span
                                    style="margin-left: 8px; opacity: 0.4">ID</span></th>
                            <th style="width: 50px">№</th>
                            <th style="width: 100px">Mijoz</th>
                            <th style="width: 50px; background: #F5F5F7 !important"><img style="margin-left: 3px"
                                                                                         src="{{asset('/img/call-hospital.svg')}}">
                            </th>
                            <th style="background: #F5F5F7 !important; width: 100px">Balance</th>
                            @php
                                $colors = ['#4B4DFF', '#00A452', '#E40089', '#ED0000'];
                            @endphp

                            @foreach($meals as $index => $meal)
                                <th style=" width: 100px;color: {{ $colors[$index % count($colors)] }};">{{ $meal->name }}</th>
                            @endforeach
                            <th style="background: #F5F5F7 !important; width: 40px"><span
                                    style="margin-left: 3px">T</span></th>
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
                                <td class="sortTable"><span
                                        style="margin-left: 8px; opacity: 0.4">{{ $order->id }}</span></td>
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
{{--                                <td style="background: #F5F5F7 !important;"--}}
{{--                                    class="{{ $order->customer->balance < 0 ? 'text-danger' : '' }}">--}}
{{--                                    {{ number_format($order->customer->balance, 0, '.', ' ') }}--}}
{{--                                </td>--}}
                                <td style="background: #F5F5F7 !important;"
                                    class="{{ optional($order->customer)->balance < 0 ? 'text-danger' : '' }}">
                                    {{ number_format(optional($order->customer)->balance ?? 0, 0, '.', ' ') }}
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
                                <td>
                                    {{ ucfirst($order->payment_method) }}
                                    @if ($order->payment_method === 'naqt')
                                        <img src="{{ asset('/img/zam-zam-cash.svg') }}" alt="Nax" width="20">
                                    @elseif ($order->payment_method === 'karta')
                                        <img src="{{ asset('/img/card.svg') }}" alt="Card" width="20">
                                    @endif
                                </td>
                                <td style="background: #F5F5F7 !important;">
                                    <strong>{{ number_format($order->total_amount, 0, ',', ' ') }}</strong>
                                </td>
                                <td>
                                    <div class="received-amount-wrapper">
                                        @php
                                            $bgColor = '';
                                            $customerType = strtolower(optional($order->customer)->type);

                                            if ($customerType === 'oylik') {
                                                $bgColor = 'color: blue;';
                                            } elseif ($order->received_amount < $order->total_amount) {
                                                $bgColor = 'color: red;';
                                            } elseif ($order->received_amount == $order->total_amount) {
                                                $bgColor = 'color: green;';
                                            }
                                        @endphp
                                        <input
                                            type="number"
                                            class="received-amount-input form-control"
                                            value="{{ $order->received_amount }}"
                                            style="width: 120px; display: inline-block; {{ $bgColor }}"
                                            max="{{ $order->total_amount }}"
                                            {{ $customerType === 'oylik' ? 'disabled' : '' }}
                                        >
                                    </div>
                                </td>



                                <td style="width: 56px">
                                    @php
                                        $customerType = strtolower(optional($order->customer)->type);
                                    @endphp

                                    <button
                                        class="btn btn-sm btn-success save-received-amount"
                                        data-order-id="{{ $order->id }}"
                                        {{ $customerType === 'oylik' ? 'disabled' : '' }}>
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
                        body: JSON.stringify({received_amount: newAmount})
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                displayAmount.textContent = `${parseInt(newAmount).toLocaleString('ru-RU')} so‘m`;

                                if (data.customer_type && data.customer_type.toLowerCase() === 'oylik') {
                                    displayAmount.style.color = 'blue';
                                } else {
                                    displayAmount.style.color = parseInt(newAmount) < data.total_amount
                                        ? 'red'
                                        : (parseInt(newAmount) === data.total_amount ? 'green' : 'black');
                                }
                            } else {
                                console.warn("Xatolik:", data.message || "Ma'lumot saqlanmadi.");
                            }
                        })
                        .catch(err => {
                            console.error("Server bilan ulanishda xatolik:", err);
                            // alert chiqarilmaydi
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
