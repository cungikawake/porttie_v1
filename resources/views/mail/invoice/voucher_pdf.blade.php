<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Demystifying Email Design</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<style>
.cardWrap {
  
  width: 27em;
  margin: 3em auto;
  color: #fff;
  font-family: sans-serif;
}

.card {
  background: #007bff;
  height: 230px;
  float: left;
  position: relative;
  padding: 1em;
  margin-top: 100px;
}

.cardLeft {
  border-top-left-radius: 8px;
  border-bottom-left-radius: 8px;
  width: 16em;
}

.cardRight {
  width: 10em;
  border-left: .18em dashed #fff;
  border-top-right-radius: 8px;
  border-bottom-right-radius: 8px;
}
.cardRight:before, .cardRight:after {
  content: "";
  position: absolute;
  display: block;
  width: .9em;
  height: .9em;
  background: #fff;
  border-radius: 50%;
  left: -.5em;
}
.cardRight:before {
  top: -.4em;
}
.cardRight:after {
  bottom: -.4em;
}

h1 {
  font-size: 1.1em;
  margin-top: 0;
}
h1 span {
  font-weight: normal;
}

.title, .name, .seat, .time {
  text-transform: uppercase;
  font-weight: normal;
}
.title h2, .name h2, .seat h2, .time h2 {
  font-size: .9em;
  color: #fff;
  margin: 0;
}
.title span, .name span, .seat span, .time span {
  font-size: .7em;
  color: #000;
}

.title {
  margin: 2em 0 0 0;
}

.name, .seat {
  margin: .7em 0 0 0;
}

.time {
  margin: .7em 0 0 1em;
}

.seat, .time {
  float: left;
}

.eye {
  position: relative;
  width: 2em;
  height: 1.5em;
  background: #fff;
  margin: 0 auto;
  border-radius: 1em/0.6em;
  z-index: 1;
}
.eye:before, .eye:after {
  content: "";
  display: block;
  position: absolute;
  border-radius: 50%;
}
.eye:before {
  width: 1em;
  height: 1em;
  background: #e84c3d;
  z-index: 2;
  left: 8px;
  top: 4px;
}
.eye:after {
  width: .5em;
  height: .5em;
  background: #fff;
  z-index: 3;
  left: 12px;
  top: 8px;
}

.number {
  text-align: center;
  text-transform: uppercase;
}
.number h3 {
  color: #e84c3d;
  margin: .9em 0 0 0;
  font-size: 2.5em;
}
.number span {
  display: block;
  color: #a2aeae;
}

.barcode {
  height: 2em;
  width: 0;
  margin: 1.2em 0 0 .8em;
  box-shadow: 1px 0 0 1px #343434, 5px 0 0 1px #343434, 10px 0 0 1px #343434, 11px 0 0 1px #343434, 15px 0 0 1px #343434, 18px 0 0 1px #343434, 22px 0 0 1px #343434, 23px 0 0 1px #343434, 26px 0 0 1px #343434, 30px 0 0 1px #343434, 35px 0 0 1px #343434, 37px 0 0 1px #343434, 41px 0 0 1px #343434, 44px 0 0 1px #343434, 47px 0 0 1px #343434, 51px 0 0 1px #343434, 56px 0 0 1px #343434, 59px 0 0 1px #343434, 64px 0 0 1px #343434, 68px 0 0 1px #343434, 72px 0 0 1px #343434, 74px 0 0 1px #343434, 77px 0 0 1px #343434, 81px 0 0 1px #343434;
}
.clear{
    clear:both;
}
</style>
<div style="background:#007bff !important; text-align:center; width:100%; height:60px;"> 
    <img src="https://porttie.com/storage/images/logo.png" height="50">
</div> 
<h1 style="font-family: sans-serif; font-size:30px; text-align:center;">Your Voucher {{ $order->hash }}</h1>
<hr>
<div class="cardWrap">
  <div class="card cardLeft">
    <h1>Ticket : <span>{{ $order->hash }} </span></h1>
    <div class="title">
      <h2>{{ $order->listing->title }}</h2>
      <span>Merchant : {{ $seller->name }}</span>
    </div>
    <div class="name">
      <h2>{{ $billing->name }}</h2>
      <span>{{ $billing->email }}</span>
    </div>
    <div class="seat">
      <h2>{{ format_money($order->amount, $order->currency) }}</h2>
      <span>Grand Total</span>
    </div>  
  </div>
  <div class="card cardRight" style="margin-top:-2px;">
    <div style="text-align:center;"> <img src="https://porttie.com/storage/images/logo.png" height="30"></div>
    <div class="number">
        {{ $order->hash }}
    </div>
    <div class="barcode">
        <img src="{{ $order->customer_details }}" style="width:150px;"> 
    </div>
  </div>
</div>

<div class="clear"></div>
<br>
<br>
<p>Terms & Conditions</p>
<p style="margin-top:-10px;"><small>* This ticket is only valid for one use, and is valid at all merchants from porttie.com.</small></p>
<p style="margin-top:-10px;"><small>* Scan this Barcode to get more detailed information.</small></p>
<hr>
<p>Contact Us</p>
<p style="margin-top:-10px;">Whatsapp : +62 811-3961-522</p>
<p style="margin-top:-10px;"><small>Email : booking@porttie.com</small></p>
<p style="margin-top:-10px;"><small>Address : Jalan Raya Kuta No.88, Kuta, Badung Regency, Bali, Indonesia</small></p>
<div style="background:#007bff !important; text-align:center; width:100%; height:60px;"> 
    <img src="https://porttie.com/storage/images/logo.png" height="50">
</div> 
</body>
</html>