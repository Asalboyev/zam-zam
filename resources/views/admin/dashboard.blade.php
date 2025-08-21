@extends('layouts.admin')

@section('title', 'Boshqaruv Paneli')

@section('css')
    <style>
        /* === Umumiy dizayn === */
        .card {
            border-radius: 12px;
            background-color: #fff;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            font-weight: 600;
            font-size: 1.1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 15px;
        }

        .card-body {
            padding: 15px;
            width: 100%;
            overflow-x: auto;
        }

        /* === Chartlar uchun moslashuvchan o'lchamlar === */
        #monthly_chart,
        #daily_chart,
        #monthly_orders_chart,
        #daily_orders_chart,
        #monthly_meals_chart,
        #daily_meals_chart,
        #monthly_order_meals_chart,
        #daily_order_meals_chart,
        #monthly_customers_chart,
        #daily_customers_chart {
            width: 100% !important;
            min-width: 300px;
            height: 450px !important;
            max-height: 500px;
        }

        /* === Grid ustunlarining moslashuvchanligi === */
        .col-md-6 {
            padding: 10px;
            transition: all 0.3s ease;
        }

        /* Kichik ekranlar uchun — har bir chart to'liq kenglikda chiqadi */
        @media (max-width: 1200px) {
            .col-md-6 {
                width: 100% !important;
            }
        }

        /* Mobil uchun — kartalar o'rtasida bo'sh joy va balandlikni kamaytirish */
        @media (max-width: 768px) {
            .card-body {
                padding: 10px;
            }

            #monthly_chart,
            #daily_chart,
            #monthly_orders_chart,
            #daily_orders_chart,
            #monthly_meals_chart,
            #daily_meals_chart,
            #monthly_order_meals_chart,
            #daily_order_meals_chart,
            #monthly_customers_chart,
            #daily_customers_chart {
                height: 350px !important;
            }
        }

        /* Juda kichik ekranlar (masalan, telefonlar 480px dan kichik) */
        @media (max-width: 480px) {
            .card-header h5 {
                font-size: 1rem;
            }

            .card-body {
                padding: 8px;
            }
        }
    </style>
@endsection


@section('content')

    <div class="container col-12">

        <div class="row ">

            <div class="col-md-3">
                <div class="card bg-white text-dark mb-3 shadow-sm">
                    <h5 class="card-header">Mijozlar</h5>
                    <div class="card-body">
                        <p class="card-text mb-1">Umumiy: <strong>{{ $customerCount }}</strong></p>
                        <p class="card-text mb-1">Oddiy: <strong>{{ $ordinaryCustomer }}</strong></p>
                        <p class="card-text mb-0">Oylik: <strong>{{ $monthlyCustomer }}</strong></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-white text-dark mb-3 shadow-sm">
                    <h5 class="card-header">Buyurtmalar</h5>
                    <div class="card-body">
                        <p class="card-text mb-1">Umumiy: <strong>{{ $orderCount }}</strong></p>
                        <p class="card-text mb-0">Oylik o‘rtacha: <strong>{{ $monthlyAverage }}</strong></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-white text-dark mb-3 shadow-sm">
                    <h5 class="card-header">Moliya</h5>
                    <div class="card-body">
                        <p class="card-text mb-1">Oylik mijozlar balansi:
                            <strong style="color: #3C4BDC">{{ number_format($monthlyBalance, 0, '.', ' ') }}</strong>
                        </p>
                        <p class="card-text mb-0">Oylik mijozlar qarzi:
                            <strong style="color: red">{{ number_format($monthlyDebt, 0, '.', ' ') }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-white text-dark mb-3 shadow-sm">
                    <h5 class="card-header">Qarzdorlik</h5>
                    <div class="card-body">
                        <p class="card-text mb-1">Qarzdorlar: <strong>{{ $debtorCount }}</strong></p>
                        <p class="card-text mb-1">To‘lanmagan buyurtmalar soni:
                            <strong>{{ $unpaidOrdersCount }}</strong></p>
                        <p class="card-text mb-0">Qarzdorlik summasi:
                            <strong class="text-danger">{{ number_format($monthlyDebt, 0, '.', ' ') }}</strong>
                        </p>
                    </div>
                </div>
            </div>
            {{-- Kunlik Daromad --}}
            <div class="row">
                {{-- Oylik Daromad --}}
                <div class="col-md-6">
                    <div class="card shadow-lg p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Oylik Daromad</h5>

                            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2">
                                <input type="month"
                                       name="start_month"
                                       value="{{ request('start_month', now()->subYear()->format('Y-m')) }}"
                                       class="form-control">

                                <input type="month"
                                       name="end_month"
                                       value="{{ request('end_month', now()->format('Y-m')) }}"
                                       class="form-control">

                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div id="monthly_chart" style="height: 450px; width:100%;"></div>
                        </div>
                    </div>
                </div>
                {{-- Kundalik Daromad --}}
                <div class="col-md-6">
                    <div class="card shadow-lg p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Kundalik Daromad</h5>

                            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2">
                                <input type="month" name="daily_date"
                                       value="{{ request('daily_date', now()->format('Y-m')) }}"
                                       class="form-control">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div id="daily_chart" style="height: 450px;"></div>
                        </div>
                    </div>
                </div>
                {{-- Kundalik Buyurtmalar soni --}}
                {{-- Oylik Buyurtmalar soni --}}
                <div class="col-md-6">
                    <div class="card shadow-lg p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Oylik Buyurtmalar soni</h5>

                            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2">
                                <input type="month"
                                       name="meals_order_start_month"
                                       value="{{ request('orders_start_month', now()->subYear()->format('Y-m')) }}"
                                       class="form-control">

                                <input type="month"
                                       name="meals_order_end_month"
                                       value="{{ request('orders_end_month', now()->format('Y-m')) }}"
                                       class="form-control">

                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div id="monthly_orders_chart" style="height: 450px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-lg p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Kundalik Buyurtmalar soni</h5>

                            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2">
                                <input type="month" name="daily_orders_date"
                                       value="{{ request('daily_orders_date', now()->format('Y-m')) }}"
                                       class="form-control">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div id="daily_orders_chart" style="height: 450px;"></div>
                        </div>
                    </div>
                </div>
                {{-- Oylik Ovqat soni --}}
                <div class="col-md-6">
                    <div class="card shadow-lg p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Oylik Ovqat soni</h5>
                            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2">
                                <input type="month"
                                       name="meals_order_start_month"
                                       value="{{ request('meals_start_month', now()->subYear()->format('Y-m')) }}"
                                       class="form-control">

                                <input type="month"
                                       name="meals_order_end_month"
                                       value="{{ request('meals_end_month', now()->format('Y-m')) }}"
                                       class="form-control">

                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            </form>

                        </div>
                        <div class="card-body">
                            <div id="monthly_meals_chart" style="height: 450px;"></div>
                        </div>
                    </div>
                </div>
                {{-- Kundalik Ovqat soni --}}
                <div class="col-md-6">
                    <div class="card shadow-lg p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Kundalik Ovqat soni</h5>
                            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2">
                                <input type="month" name="daily_meals_date"
                                       value="{{ request('daily_meals_date', now()->format('Y-m')) }}"
                                       class="form-control">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            </form>

                        </div>
                        <div class="card-body">
                            <div id="daily_meals_chart" style="height: 450px;"></div>
                        </div>
                    </div>
                </div>

                {{-- Oylik Ovqat soni --}}
                <div class="col-md-6">
                    <div class="card shadow-lg p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Oylik Oldi-Sotdi</h5>
                            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2">
                                <input type="month"
                                       name="meals_order_start_month"
                                       value="{{ request('meals_order_start_month', now()->subYear()->format('Y-m')) }}"
                                       class="form-control">

                                <input type="month"
                                       name="meals_order_end_month"
                                       value="{{ request('meals_order_end_month', now()->format('Y-m')) }}"
                                       class="form-control">

                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            </form>

                        </div>
                        <div class="card-body">
                            <div id="monthly_order_meals_chart" style="height: 450px;"></div>
                        </div>
                    </div>
                </div>
                {{-- Kundalik Ovqat soni --}}
                <div class="col-md-6">
                    <div class="card shadow-lg p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Kundalik Oldi-Sotdi</h5>
                            @php
                                $currentMonth = request('daily_meals_order_date', \Carbon\Carbon::now()->format('Y-m'));
                            @endphp

                            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2">
                                <input type="month" name="daily_meals_order_date" value="{{ $currentMonth }}" class="form-control">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            </form>

                        </div>
                        <div class="card-body">
                            <div id="daily_order_meals_chart" style="height: 450px;"></div>
                        </div>
                    </div>
                </div>

                {{-- Oylik mijozlar soni --}}
                <div class="col-md-6">
                    <div class="card shadow-lg p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Oylik Mijozlar o‘sishi</h5>
                            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2">
                                <input type="month"
                                       name="clients_start_month"
                                       value="{{ request('clients_start_month', now()->subYear()->format('Y-m')) }}"
                                       class="form-control">

                                <input type="month"
                                       name="clients_end_month"
                                       value="{{ request('clients_end_month', now()->format('Y-m')) }}"
                                       class="form-control">

                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div id="monthly_customers_chart" style="height: 450px;"></div>
                        </div>
                    </div>
                </div>

                {{-- Kundalik mijozlar soni --}}
                <div class="col-md-6">
                    <div class="card shadow-lg p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Kundalik Mijozlar o‘sishi</h5>
                            @php
                                use Carbon\Carbon;
                                $currentMonth = request('daily_clients_date', Carbon::now()->format('Y-m'));
                            @endphp

                            <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2">
                                <input type="month" name="daily_clients_date" value="{{ $currentMonth }}"
                                       class="form-control">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            </form>
                        </div>

                        <div class="card-body">
                            <div id="daily_customers_chart" style="height: 400px;"></div>

                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

@endsection

@section('js')
    {{-- Google Charts --}}
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            // === Daily Chart (Daromad) ===
            var dailyData = google.visualization.arrayToDataTable([
                ['Sana', 'Daromad'],
                    @foreach($dailyLabels as $index => $date)
                ['{{ $date }}', {{ $dailySalesData[$index] ?? 0 }}],
                @endforeach
            ]);
            var dailyOptions = {
                title: 'Kundalik Daromad',
                curveType: 'function',
                legend: {position: 'bottom'},
                vAxis: {minValue: 0},
                colors: ['#1e88e5'],
                lineWidth: 3,
                pointSize: 6,
                backgroundColor: '#ffffff',
                chartArea: {width: '85%', height: '70%'}
            };
            var dailyChart = new google.visualization.LineChart(document.getElementById('daily_chart'));
            dailyChart.draw(dailyData, dailyOptions);

            // === Monthly Chart (Daromad) ===
            var monthlyData = google.visualization.arrayToDataTable([
                ['Oy', 'Daromad'],
                    @foreach($monthlyLabels as $index => $month)
                ['{{ $month }}', {{ $monthlySalesData[$index] ?? 0 }}],
                @endforeach
            ]);
            var monthlyOptions = {
                title: 'Oylik Daromad',
                curveType: 'function',
                legend: {position: 'bottom'},
                vAxis: {minValue: 0},
                colors: ['#43a047'],
                lineWidth: 3,
                pointSize: 6,
                backgroundColor: '#ffffff',
                chartArea: {width: '85%', height: '70%'}
            };
            var monthlyChart = new google.visualization.LineChart(document.getElementById('monthly_chart'));
            monthlyChart.draw(monthlyData, monthlyOptions);

            // === Daily Chart (Buyurtmalar soni) ===
            var dailyOrdersData = google.visualization.arrayToDataTable([
                ['Sana', 'Buyurtmalar'],
                    @foreach($dailyOrdersLabels as $index => $date)
                ['{{ $date }}', {{ $dailyOrdersData[$index] ?? 0 }}],
                @endforeach
            ]);
            var dailyOrdersOptions = {
                title: 'Kundalik Buyurtmalar soni',
                curveType: 'function',
                legend: {position: 'bottom'},
                vAxis: {minValue: 0},
                colors: ['#f4511e'],
                lineWidth: 3,
                pointSize: 6,
                backgroundColor: '#ffffff',
                chartArea: {width: '85%', height: '70%'}
            };
            var dailyOrdersChart = new google.visualization.LineChart(document.getElementById('daily_orders_chart'));
            dailyOrdersChart.draw(dailyOrdersData, dailyOrdersOptions);

            // === Monthly Chart (Buyurtmalar soni) ===
            var monthlyOrdersData = google.visualization.arrayToDataTable([
                ['Oy', 'Buyurtmalar'],
                    @foreach($monthlyOrdersLabels as $index => $month)
                ['{{ $month }}', {{ $monthlyOrdersData[$index] ?? 0 }}],
                @endforeach
            ]);
            var monthlyOrdersOptions = {
                title: 'Oylik Buyurtmalar soni',
                curveType: 'function',
                legend: {position: 'bottom'},
                vAxis: {minValue: 0},
                colors: ['#8e24aa'],
                lineWidth: 3,
                pointSize: 6,
                backgroundColor: '#ffffff',
                chartArea: {width: '85%', height: '70%'}
            };
            var monthlyOrdersChart = new google.visualization.LineChart(document.getElementById('monthly_orders_chart'));
            monthlyOrdersChart.draw(monthlyOrdersData, monthlyOrdersOptions);

            // === Daily Chart (Ovqat soni) ===
            var dailyMealsData = google.visualization.arrayToDataTable([
                ['Sana', 'Ovqat soni'],
                    @foreach($dailyMealsLabels as $index => $date)
                ['{{ $date }}', {{ $dailyMealsData[$index] ?? 0 }}],
                @endforeach
            ]);
            var dailyMealsOptions = {
                title: 'Kundalik Ovqat soni',
                curveType: 'function',
                legend: {position: 'bottom'},
                vAxis: {minValue: 0},
                colors: ['#ff9800'],
                lineWidth: 3,
                pointSize: 6,
                backgroundColor: '#ffffff',
                chartArea: {width: '85%', height: '70%'}
            };
            var dailyMealsChart = new google.visualization.LineChart(document.getElementById('daily_meals_chart'));
            dailyMealsChart.draw(dailyMealsData, dailyMealsOptions);

            // === Monthly Chart (Ovqat soni) ===
            var monthlyMealsData = google.visualization.arrayToDataTable([
                ['Oy', 'Ovqat soni'],
                    @foreach($monthlyMealsLabels as $index => $month)
                ['{{ $month }}', {{ $monthlyMealsData[$index] ?? 0 }}],
                @endforeach
            ]);
            var monthlyMealsOptions = {
                title: 'Oylik Ovqat soni',
                curveType: 'function',
                legend: {position: 'bottom'},
                vAxis: {minValue: 0},
                colors: ['#009688'],
                lineWidth: 3,
                pointSize: 6,
                backgroundColor: '#ffffff',
                chartArea: {width: '85%', height: '70%'}
            };
            var monthlyMealsChart = new google.visualization.LineChart(document.getElementById('monthly_meals_chart'));
            monthlyMealsChart.draw(monthlyMealsData, monthlyMealsOptions);
        }
    </script>
    <script type="text/javascript">
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            // Backenddan kelgan datasetlar
            const dailyOlindi = @json($dailyOlindiData);
            const dailySotildi = @json($dailySotildiData);
            const dailyQoldi = @json($dailyQoldiData);

            const monthlyOlindi = @json($monthlyOlindiData);
            const monthlySotildi = @json($monthlySotildiData);
            const monthlyQoldi = @json($monthlyQoldiData);

            // === KUNLIK OVQAT GRAFIK ===
            let dailyData = new google.visualization.DataTable();
            dailyData.addColumn('string', 'Kun');
            dailyData.addColumn('number', 'Olindi');
            dailyData.addColumn('number', 'Sotildi');
            dailyData.addColumn('number', 'Qoldi');

            for (let i = 0; i < dailyOlindi.length; i++) {
                dailyData.addRow([
                    (i + 1).toString(),
                    dailyOlindi[i],
                    dailySotildi[i],
                    dailyQoldi[i]
                ]);
            }

            let dailyOptions = {
                // title: '📊 Kundalik Ovqat soni',
                curveType: 'function',
                legend: {position: 'bottom', textStyle: {fontSize: 12, bold: true}},
                colors: ['#1E90FF', '#28A745', '#DC3545'], // Olindi - ko‘k, Sotildi - yashil, Qoldi - qizil
                lineWidth: 3,
                pointSize: 6,
                backgroundColor: '#f9f9f9',
                chartArea: {width: '85%', height: '70%'},
                vAxis: {
                    gridlines: {color: '#eee'},
                    minValue: 0,
                    textStyle: {fontSize: 12}
                },
                hAxis: {
                    textStyle: {fontSize: 12}
                },
                tooltip: {isHtml: true}
            };

            let dailyChart = new google.visualization.LineChart(document.getElementById('daily_order_meals_chart'));
            dailyChart.draw(dailyData, dailyOptions);

            // === OYLIK OVQAT GRAFIK ===
            let monthlyData = new google.visualization.DataTable();
            monthlyData.addColumn('string', 'Oy');
            monthlyData.addColumn('number', 'Olindi');
            monthlyData.addColumn('number', 'Sotildi');
            monthlyData.addColumn('number', 'Qoldi');

            for (let i = 0; i < monthlyOlindi.length; i++) {
                monthlyData.addRow([
                    (i + 1).toString(),
                    monthlyOlindi[i],
                    monthlySotildi[i],
                    monthlyQoldi[i]
                ]);
            }

            let monthlyOptions = {
                // title: '📅 Oylik Ovqat soni',
                curveType: 'function',
                legend: {position: 'bottom', textStyle: {fontSize: 12, bold: true}},
                colors: ['#1E90FF', '#28A745', '#DC3545'],
                lineWidth: 3,
                pointSize: 6,
                backgroundColor: '#f9f9f9',
                chartArea: {width: '85%', height: '70%'},
                vAxis: {
                    gridlines: {color: '#eee'},
                    minValue: 0,
                    textStyle: {fontSize: 12}
                },
                hAxis: {
                    textStyle: {fontSize: 12}
                },
                tooltip: {isHtml: true}
            };

            let monthlyChart = new google.visualization.LineChart(document.getElementById('monthly_order_meals_chart'));
            monthlyChart.draw(monthlyData, monthlyOptions);
        }
    </script>
    <script type="text/javascript">
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            // === Backenddan kelgan datasetlar ===
            const dailyCustomers = @json($dailyClientsData);
            const dailyLabels = @json($dailyClientsLabels);
            const monthlyCustomers = @json($monthlyClientsData);
            const monthlyLabels = @json($monthlyClientsLabels);

            // === KUNLIK MIJOZLAR ===
            let dailyCustData = new google.visualization.DataTable();
            dailyCustData.addColumn('string', 'Kun');
            dailyCustData.addColumn('number', 'Yangi mijozlar');

            for (let i = 0; i < dailyCustomers.length; i++) {
                dailyCustData.addRow([dailyLabels[i], dailyCustomers[i]]);
            }

            let dailyCustOptions = {
                title: 'Kundalik mijozlar o‘sishi',
                curveType: 'function',
                legend: {position: 'bottom'},
                colors: ['#FF9800'], // orange
                hAxis: {title: 'Kun'},
                vAxis: {title: 'Mijozlar soni'},
                pointSize: 6,
                backgroundColor: '#fafafa'
            };

            let dailyCustChart = new google.visualization.LineChart(document.getElementById('daily_customers_chart'));
            dailyCustChart.draw(dailyCustData, dailyCustOptions);

            // === OYLIK MIJOZLAR ===
            let monthlyCustData = new google.visualization.DataTable();
            monthlyCustData.addColumn('string', 'Oy');
            monthlyCustData.addColumn('number', 'Yangi mijozlar');

            for (let i = 0; i < monthlyCustomers.length; i++) {
                monthlyCustData.addRow([monthlyLabels[i], monthlyCustomers[i]]);
            }

            let monthlyCustOptions = {
                title: 'Oylik mijozlar o‘sishi',
                curveType: 'function',
                legend: {position: 'bottom'},
                colors: ['#FF5722'], // deep orange
                hAxis: {title: 'Oy'},
                vAxis: {title: 'Mijozlar soni'},
                pointSize: 6,
                backgroundColor: '#fafafa'
            };

            let monthlyCustChart = new google.visualization.LineChart(document.getElementById('monthly_customers_chart'));
            monthlyCustChart.draw(monthlyCustData, monthlyCustOptions);
        }
    </script>

@endsection
