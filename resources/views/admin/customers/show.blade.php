@php
    function getMealName($id, $meals) {
        foreach ($meals as $m) {
            if ($m->id == $id) return $m->name;
        }
        return '-';
    }

    $totalMeals = 0;
@endphp

    @extends('layouts.admin')
@section('title')
    Show Category
@endsection
@section('content')

    <div class="col-12 col-md-12 col-lg-12">
        <div class="row">
            <div class="col-4">
                <div class="card mb-0">
                    <div class="card-body">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('admin.customers.edit',$customer->id) }}">Anketa <span class="badge badge-white"></span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="#">Buyurtmalar <span class="badge badge-primary"></span></a>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @if(isset($latestOrders) && count($latestOrders) > 0)
            <div class="card mt-">
                <div class="card-header" style="display: flex; align-items: center; justify-content: space-between">
                    <h5>Buyurtmalar tarixi </h5>
{{--                    <div class="card-header-form" style="text-align: right">--}}
{{--                        <a  href="{{ route('admin.customers.edit',$customer->id) }}" class="btn btn-primary">Anketa</a>--}}
{{--                    </div>--}}
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mijoz</th>
                            <th>Telefon</th>
                            <th>Sana</th>
                            <th style="color: #4B4DFF">Ovaqt1</th>
                            <th style="color: #00A452;">Ovaqt2</th>
                            <th style="color: #E40089;">Ovaqt3</th>
                            <th style="color: #ED0000;">Ovaqt4</th>
                            <th>T</th>
                            <th>Cola</th>
                            <th>Dostavka</th>
                            <th>Kuryer</th>
                            <th>To‘lov</th>
                            <th>Jami</th>
                            <th>Olindi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($latestOrders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->customer->name ?? '-' }}</td>
                                <td>{{ $order->customer->phone ?? '-' }}</td>
                                <td>{{ $order->order_date }}</td>



                                {{-- Ovqat 1 --}}
                                <td>
                                    @if($order->meal_1_id)
                                        {{ getMealName($order->meal_1_id, $meals) }} ({{ $order->meal_1_quantity }})
                                        @php $totalMeals += $order->meal_1_quantity; @endphp
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Ovqat 2 --}}
                                <td>
                                    @if($order->meal_2_id)
                                        {{ getMealName($order->meal_2_id, $meals) }} ({{ $order->meal_2_quantity }})
                                        @php $totalMeals += $order->meal_2_quantity; @endphp
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Ovqat 3 --}}
                                <td>
                                    @if($order->meal_3_id)
                                        {{ getMealName($order->meal_3_id, $meals) }} ({{ $order->meal_3_quantity }})
                                        @php $totalMeals += $order->meal_3_quantity; @endphp
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Ovqat 4 --}}
                                <td>
                                    @if($order->meal_4_id)
                                        {{ getMealName($order->meal_4_id, $meals) }} ({{ $order->meal_4_quantity }})
                                        @php $totalMeals += $order->meal_4_quantity; @endphp
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><strong>{{ $order->total_meals }}</strong></td>
                                <td>{{ $order->cola_quantity }}</td>
                                <td>{{ number_format($order->delivery_fee, 0, ',', ' ') }} so‘m</td>
                                <td>{{ $order->driver->name ?? '-' }}</td>
                                <td>{{ ucfirst($order->payment_method) }}</td>
                                <td><strong>{{ number_format($order->total_amount, 0, ',', ' ') }} so‘m</strong></td>
                                <td>
                                    <strong style="color: {{ $order->received_amount < $order->total_amount ? 'red' : 'green' }}">
                                        {{ number_format($order->received_amount, 0, ',', ' ') }} so‘m
                                    </strong>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $latestOrders->links() }}

            </div>
        @endif
    </div>
@endsection
