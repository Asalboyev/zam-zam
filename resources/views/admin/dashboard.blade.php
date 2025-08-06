@extends('layouts.admin')

@section('title', 'Boshqaruv Paneli')

@section('css')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <!-- Statistik kartalar -->
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Ovqatlar</div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $mealCount }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Mijozlar</div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $customerCount }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">Haydovchilar</div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $driverCount }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-header">Bugungi Savdo</div>
                    <div class="card-body">
                        <h5 class="card-title">{{ number_format($dailySales, 0, ',', ' ') }} so'm</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik (So‘nggi 7 kun savdosi) -->
        <div class="card mt-4">
            <div class="card-header">
                So‘nggi 7 kunlik savdo grafigi
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [{
                    label: 'Savdo (so‘m)',
                    data: {!! json_encode($last7Days) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.3)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' so‘m';
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
