<?php

namespace App\Http\Controllers\Account;

use App\Mail\AcceptPurchase;
use App\Mail\DeclinePurchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Mail;
use Talk;
use QrCode;
use App\Models\Role;
#use Priotas\Twig\Extension\QrCode;
class DashboardController extends Controller
{

    public function index()
    {
        $user = auth()->user(); 
        if($user->hasRole('Vendor')){

            $orders = Order::with('listing', 'user')->where('seller_id', auth()->user()->id)->where('status', 'accepted')->orderBy('id', 'DESC')->get();
            
            $data['total_sales'] = 0;
            $data['total_earning'] = 0;
            $data['total_wd'] = 0;
            foreach($orders as $order){
                $data['total_sales'] += $order->amount;
                $data['total_earning'] += $order->amount - $order->service_fee;
            }
            
            $data['finish_order'] = 0;
            $finish_order = Order::with('listing', 'user')->where('seller_id', auth()->user()->id)
                        ->where('status', 'accepted')
                        ->orderBy('id', 'DESC')->count();
            $data['finish_order'] = $finish_order;
            
            $data['pandding_order'] = 0;
            $pandding_order = Order::with('listing', 'user')->where('seller_id', auth()->user()->id)
                        ->where('status', 'open')
                        ->orderBy('id', 'DESC')->count();
            $data['pandding_order'] = $pandding_order;
            $data['total_order'] = 0;
            if($data['finish_order'] > 0 || $data['pandding_order'] > 0){
                $data['total_order'] = ($data['finish_order'] / ($data['finish_order'] + $data['pandding_order'])) * 100;
            }
            
            return view('account.dashboard', compact('data'));
        }else{
            return view('account.vendor_false');
        }
    }
 
}
