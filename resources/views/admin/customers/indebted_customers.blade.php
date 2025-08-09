@extends('layouts.admin')
@section('title')
    Customers
@endsection
@section('css')
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

    <style>
        .eye-btn {
            display: inline-flex;
            align-items: center;
            text-align: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: 2px solid #dcdce6;
            border-radius: 12px;
            background-color: white;
            color: #2f2f41;
            font-size: 22px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .eye-btn:hover {
            background-color: #f0f0f5;
            color: #000;
            border-color: #b4b4cc;
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

        .icon-btn.delete-btn {
            color: #c0392b;
            border-color: #f0dcdc;
        }

        .icon-btn.delete-btn:hover {
            background-color: #ffeaea;
            border-color: #e99b9b;
        }

    </style>

@endsection

@section('content')

    <div class="col-12 col-md-12 col-lg-12">

        <div class="row">
            <div class="col-5 mb-3">
                <div class="card mb-0">
                    <div class="card-body">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link " href="{{ route('admin.ordinary_debt') }}">Odiy Qarizdorlar <span
                                        class="badge badge-white"></span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link   " href="{{route('admin.monthly_debtors')}}">Oylik Qarizdorlar <span
                                        class="badge badge-primary"></span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('admin.indebted_customers') }}">Qarizdor
                                    Mijozlar <span class="badge badge-primary"></span></a>

                        </ul>
                    </div>
                </div>
            </div>
        </div>

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
                <h4>Qarizdor Mijozlar</h4>


            </div>
            <div class="card-body">

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>
                            <tr>
                                <th class="text-center">
                                    ID
                                </th>
                                <th>Fio</th>
                                <th>Tel raqam</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Balans</th>
                                <th>Telegram</th>
                                <th>Tuman</th>
                                <th>Manzil</th>
                            </tr>
                            @foreach($customers as $iteam)
                                <tr>
                                    <td class="p-0 text-center">
                                        {{$iteam->id}}
                                    </td>
                                    <td>
                                        @if(isset($iteam->id) && $iteam->type === 'oylik')
                                            <span style="color: blue;">{{ $iteam->name }}</span>
                                        @else
                                            {{ $iteam->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td> {{$iteam->phone}}</td>
                                    <td> {{$iteam->type}}   </td>
                                    <td> {{$iteam->status}}   </td>
                                    <td class="{{ $iteam->balance < 0 ? 'text-danger' : '' }}">{{ number_format($iteam->balance, 0, '.', ' ') }} </td>

                                    <td>{{$iteam->telegram}}</td>
                                    <td>{{ $iteam->getRegion()->name ?? '-' }}</td>

                                    {{--                                <td>{{$iteam->address}}</td>--}}
                                    <td>{{$iteam->district}}</td>

                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <nav class="d-inline-block">
                    {{ $customers->links() }}

                    {{-- <ul class="pagination mb-0">
                      <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1"><i class="fas fa-chevron-left"></i></a>
                      </li>
                      <li class="page-item active"><a class="page-link" href="#">1 <span class="sr-only">(current)</span></a></li>
                      <li class="page-item">
                        <a class="page-link" href="#">2</a>
                      </li>
                      <li class="page-item"><a class="page-link" href="#">3</a></li>
                      <li class="page-item">
                        <a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>
                      </li>
                    </ul> --}}
                </nav>
            </div>
        </div>
    </div>
@endsection
