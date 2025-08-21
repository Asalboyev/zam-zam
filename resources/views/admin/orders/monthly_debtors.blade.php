@php
    $colors = ['#4B4DFF', '#00A452', '#E40089', '#ED0000'];
     $planByMethod = ['karta' => 0, 'naqt' => 0];
    $factByMethod = ['karta' => 0, 'naqt' => 0];

    $oylikPlan = 0;
    $oylikFact = 0;

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
        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border: 2px solid #dcdce6;
            border-radius: 12px;
            background-color: white;
            color: #2f2f41;
            font-size: 20px;
            transition: all 0.2s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .icon-btn:hover {
            background-color: #f0f0f5;
            color: #000;
            border-color: #b4b4cc;
        }


    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-5 mb-3">
            <div class="card mb-0">
                <div class="card-body">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link " href="{{ route('admin.ordinary_debt') }}">Odiy Qarzdorlar  <span class="badge badge-white"></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link  active " href="{{route('admin.monthly_debtors')}}">Oylik Qarzdorlar  <span class="badge badge-primary"></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="{{ route('admin.indebted_customers') }}">Qarzdor Mijozlar <span class="badge badge-primary"></span></a>

                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="col-19 col-md-19 col-lg-19">

        @if(isset($customers) && count($customers) > 0)
            <div class="card mt-2">
                <div class="card-header">
                    <h5>Oylik Qarizdorlar </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 customTable">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Buyurtma №</th>
                            <th>Mijoz</th>
                            <th>Telefon</th>
                            <th>Balans</th>
                            <th>Telegram</th>
                            <th>Jami Ovqat</th>
                            <th>Jami summa</th>
                            <th></th>

                        </tr>
                        </thead>
                        <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td>{{ $customer->id }}</td>
                                <td>{{ $customer->lastOrder->daily_order_number ?? '-' }}</td>
                                <td>
                                    @if($customer->type === 'oylik')
                                        <span style="color: blue;">{{ $customer->name }}</span>
                                    @else
                                        {{ $customer->name }}
                                    @endif
                                </td>
                                <td onclick="showPhoneTooltip(this, '{{ $customer->phone ?? '' }}')">
                                    @if ($customer->phone)
                                        <img src="{{ asset('/img/call-hospital.svg') }}" alt="Call" style="cursor: pointer;">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="{{ $customer->balance < 0 ? 'text-danger' : '' }}">
                                    {{ number_format($customer->balance, 0, '.', ' ') }}
                                </td>
                                <td>{{ $customer->telegram }}</td>

                                @php
                                    // Ovqatlar sonini hisoblash
                                    $totalMeals = 0;
                                    $mealQuantities = [];
                                    if ($customer->lastOrder) {
                                        $mealIds = [
                                            $customer->lastOrder->meal_1_id => $customer->lastOrder->meal_1_quantity,
                                            $customer->lastOrder->meal_2_id => $customer->lastOrder->meal_2_quantity,
                                            $customer->lastOrder->meal_3_id => $customer->lastOrder->meal_3_quantity,
                                            $customer->lastOrder->meal_4_id => $customer->lastOrder->meal_4_quantity,
                                        ];
                                        foreach ($meals as $meal) {
                                            $qty = $mealIds[$meal->id] ?? 0;
                                            $mealQuantities[$meal->id] = $qty;
                                            $totalMeals += $qty;
                                        }
                                    }
                                @endphp
                                <td><strong>{{ $totalMeals }}</strong></td>
                                <td><strong>{{ isset($customer->lastOrder->total_amount) ? number_format($customer->lastOrder->total_amount, 0, ',', ' ') : '-' }}</strong></td>
                                <td>
                                    <a href="{{ route('admin.customers.edit', $customer->id ) }}" class="icon-btn">
                                        <i class="fas fa-eye"></i>
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
            document.querySelectorAll('.customer-select').forEach(function(selectEl) {
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
                        body: JSON.stringify({ received_amount: newAmount })
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
