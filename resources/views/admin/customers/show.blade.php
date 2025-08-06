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
        @if(isset($latestOrders) && count($latestOrders) > 0)
            <div class="card mt-2">
                <div class="card-header" style="display: flex; align-items: center; justify-content: space-between">
                    <h5>Buyurtmalar tarixi </h5>
                    <div class="card-header-form" style="text-align: right">
                        <a  href="{{ route('admin.customers.edit',$customer->id) }}" class="btn btn-primary">Anketa</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mijoz</th>
                            <th>Telefon</th>
                            <th>Sana</th>
                            <th>Ovaqt1</th>
                            <th>Ovaqt2</th>
                            <th>Ovaqt3</th>
                            <th>Ovaqt4</th>
                            <th>T</th>
                            <th>Cola</th>
                            <th>Dostavka</th>
                            <th>Kuryer</th>
                            <th>To‘lov</th>
                            <th>Jami</th>
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

                                <td><strong>{{ $totalMeals }}</strong></td>
                                <td>{{ $order->cola_quantity }}</td>
                                <td>{{ number_format($order->delivery_fee, 0, ',', ' ') }} so‘m</td>
                                <td>{{ $order->driver->name ?? '-' }}</td>
                                <td>{{ ucfirst($order->payment_method) }}</td>
                                <td><strong>{{ number_format($order->total_amount, 0, ',', ' ') }} so‘m</strong></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
