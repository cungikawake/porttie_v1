<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Number - #{{ $order_number }}</title>

    <style type="text/css">
        @page {
            margin: 0px;
        }
        body {
            margin: 0px;
        }
        * {
            font-family: Verdana, Arial, sans-serif;
        }
        a {
            color: #fff;
            text-decoration: none;
        }
        table {
            font-size: x-small;
        }
        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }
        .invoice table {
            margin: 15px;
        }
        .invoice h3 {
            margin-left: 15px;
        }
        .information {
            background-color: #60A7A6;
            color: #FFF;
        }
        .information .logo {
            margin: 5px;
        }
        .information table {
            padding: 10px;
        }
    </style>

</head>
<body>

<div class="information">
    <table width="100%">
        <tr>
            <td align="left" style="width: 40%;">
                
            </td>
            <td align="center">
                <h1>Porttie.com</h1>
                <h3>Your Voucher</h3>
            </td>
            <td align="right" style="width: 40%;">
                <img src="{{ $order->customer_details }}" style="width:150px;"> 
                
            </td>
        </tr>

    </table>
</div>


<br/>

<div class="invoice">
    <h3>Invoice specification : {{ $order_number }} ( <em style="font-size: 18px; color:#42d159; text-align: left;">{{ $order->status }}</em> )</h3>
    <table width="100%">
        <thead style="border-bottom: solid 1px #000;">
	        <tr>
	            <th>Item</th>
	            <th>Description</th>
	            <th>Total</th>
	        </tr>
        </thead>
        <tbody style="border-bottom: 1px #000 solid;">
	        <tr>
	            <td>
	            	<h3>{{ $order->listing->title }}</h3>
	            	<img class="img img-thumbnail p-0"  src="{{ $order->listing->thumbnail }}" style="width: 150px;">
	            </td>
	            <td>
	            	@foreach($order->user_choices as $choice )
			      		<p><b>{{ $choice->name }}</b> : {{ $choice->value }}<br /></p>
			      	@endforeach
	            </td>
	            <td align="left">
	            	<p>{{ format_money($order->amount, $order->currency) }}</p>
	            	<p></p>
	            </td>
	        </tr> 
        </tbody>

        <tfoot style="border-bottom: 1px #000 solid;">
            <tr >
                <td></td>
                <td align="left"><h3>Total</h3></td>
                <td align="left">
                    <h3>{{ format_money($order->amount, $order->currency) }}</h3>
                
                </td>
            </tr>
        </tfoot>
    </table>
    <table width="100%" style="border-bottom: 1px #000 solid;">
        <tr>
            <td style="widht:40%">
                <h3>Merchant Name</h3>
                 
                <p>{{ $seller->name }}</p>
                <p style="margin-top: -10px;">Email : {{ $seller->email }}</p>
                <p style="margin-top: -10px;">Address : {{ $seller->country_name.', '.$billing->region.', '.$billing->city }}</p>
                
            </td>
            <td style="widht:20%"></td>
            <td style="widht:40%">
                <h3>Billing Information</h3>

                <p>Fullname : {{ $billing->name }}</p>
                <p style="margin-top: -10px;">Email : {{ $billing->email }}</p>
                <p style="margin-top: -10px;">Address : {{ $billing->country_name.', '.$billing->region.', '.$billing->city }}</p>
            
            </td>
        </tr>
    </table>
</div>

<div class="information" style="position: absolute; bottom: 0;">
    <table width="100%">
        <tr>
            <td align="left" style="width: 50%;">
                &copy; {{ date('Y') }} {{ config('app.url') }} - All rights reserved.
            </td>
            <td align="right" style="width: 50%;">
                www.porttie.com
            </td>
        </tr>

    </table>
</div>
</body>
</html>