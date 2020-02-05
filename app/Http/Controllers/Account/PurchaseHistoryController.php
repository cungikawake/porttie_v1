<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use PDF;
class PurchaseHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
		$orders = Order::with('listing', 'listing.user')->where('user_id', auth()->user()->id)->orderBy('id', 'DESC')->paginate(15);
		return view('account.purchase_history.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($order)
    {
		return view('account.purchase_history.show', compact('order'));
    }


    /**
     * voucher the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download($order)
    {
         
        $order_data = Order::where('hash',$order)->first();
        dd($order_data);
		return view('account.purchase_history.index', compact('order'));
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($order)
    {  
        
        
        $data['order'] = $order;
        $data['billing'] = $order->user;
        $data['seller'] = $order->seller; 

        $order = Order::findOrFail($order->id); 

        \QrCode::size(500)
        ->format('png')
        ->generate(url('/account/orders/'.$order->hash), public_path('images/qrcode_'.$order->id.'.png'));
   
        $order->customer_details = 'images/qrcode_'.$order->id.'.png';
        $order->save();
        
        //return view('mail.invoice.voucher_pdf', $data);

        $pdf = PDF::loadView('mail.invoice.voucher_pdf', $data);
        return  $pdf->download('voucher_porttie_'.$order->hash.'.pdf'); 
         
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
