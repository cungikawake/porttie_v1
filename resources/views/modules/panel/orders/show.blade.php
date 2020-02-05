@extends('panel::layouts.master')

@section('content')
    <a href="{{ route('panel.orders.index') }}" class="mb-1"><i class="fa fa-angle-left" aria-hidden="true"></i> back</a>

    <div class="row mb-3">
        <div class="col-sm-8">
            <h2  class="mt-xxs">Viewing order : {{$order->hash}}</h2>
        </div>
        <div class="col-sm-4">
            {{$order->listing->title}}
        </div>

    </div>


    <div class="row">

        <div class="col-sm-12">

            <table class="table table-striped">
                <tbody>
                    <tr>
                        <th scope="row">Listing</th>
                        <td>{{$order->listing->title}}</td>
                    </tr>
                    <tr>
                        <th scope="row">Status</th>
                        <td><span class="badge badge-warning">{{$order->status}}</span></td>
                    </tr>
                    <tr>
                        <th scope="row">Amount</th>
                        <td>{{$order->currency}} {{ number_format($order->amount) }} </td>
                    </tr>
                    <tr>
                        <th scope="row">Service fee</th>
                        <td>{{$order->currency}} {{ number_format($order->service_fee) }} </td>
                    </tr>
                    <tr>
                        <th scope="row">Buyer</th>
                        <td>{{$order->user->name}} ({{$order->user->email}})
                             
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Seller</th>
                        <td>{{$order->listing->user->name}} ({{$order->listing->user->email}})

                            @if($order->payment_gateway->name == 'ipaymu')
                                - Payment Method : <span class="badge badge-success">Ipaymu</span> 
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Date&nbsp;Placed</th>
                        <td>{{$order->created_at->toDayDateTimeString()}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
