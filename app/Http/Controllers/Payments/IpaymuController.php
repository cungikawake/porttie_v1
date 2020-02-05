<?php

namespace App\Http\Controllers\Payments;

use App\Models\CheckoutSession;
use App\Models\PaymentGateway;
use App\Models\PaymentProvider;
use Hashids;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Listing;
use App\Http\Requests\UpdateUserProfile;
use Image;
use Storage;
use GeoIP;
use Date;
use URL;
use App\Support\PaypalClassic;
use Socialite;
use App\Events\OrderPlaced;
use Carbon\Carbon;
use Mail;
use App\Mail\AcceptPurchase;

class IpaymuController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function start($session, Request $request) {
      
        $listing = $session->listing;
		$gateway = $listing->user->payment_gateway($session->payment_provider->key);
       
        if(!$gateway) {
            dd("NO SELLER ID");
        }
 
        #calculate the real price of the order
        $widget = '\App\Widgets\Order\\'.studly_case($listing->pricing_model->widget).'Widget';
        $widget = new $widget();
        $result = $widget->calculate_price($listing, $session->request);
         
        $query_params = collect($session->request)->reject(function ($k, $v) {
            return substr( $v, 0, 3 ) === "ic-" || substr( $v, 0, 1 ) === "_";
        });

        $query_params['listing_id'] = $listing->id;
        $query_string = '';
        if($query_params)
            $query_string = http_build_query($query_params->toArray());
        
        #seting params
        $ipaymu_key = $gateway->token;
          
        $qty = $session->request['quantity']?$session->request['quantity']:1;
        $ureturn = route('payments.ipaymu.return', ['session' => $session->id],true);
        $unotify = route('payments.ipaymu.notify', ['session' => $session->id],true);
         
        $ucancel = route('payments.ipaymu.cancel', ['session' => $session->id],true);
        if(url()->previous())
			$ucancel = url()->previous();
        
        $url = 'https://my.ipaymu.com/payment';  // URL Payment iPaymu           
        $params = array(   // Prepare Parameters            
            'key'      => $ipaymu_key, // API Key Merchant / Penjual
            'action'   => 'payment',
            'product'  => array($listing->title),
            'price'    => array($result['total']), //  Harga per totoal item
            'quantity' => array(1), //  Total Qty per cart
            'comments' => 'Payment for Porttie.com', // Optional
            'ureturn'  => $ureturn,
            'unotify'  => $unotify,
            'ucancel'  => $ucancel,
            
            /* Parameter tambahan untuk pembayaran COD
            * ----------------------------------------------- */
            #'weight'     => array(1, 1), // Berat barang (satuan kilo, menerima array)
            #'dimensi'    => array('1:2:1', '1:1:1'), // Dimensi barang (format => panjang:lebar:tinggi, menerima array)
           #'postal_code'=> '80361',  // Kode pos untuk custom pikcup
            #'address'    => 'Jalan Raya Kuta, No 88R, Badung, Bali', // Alamat untuk custom pickup
            /* ----------------------------------------------- */
            
            /* Parameter tambahan untuk custom payment page (hanya menampilkan satu metode pembayaran)
            * ----------------------------------------------- */
            #'pay_method'  => 'arthagraha', // Metode pembayaran yang akan ditampilkan (VA BAG => arthagraha, VA Niaga => niaga, VA BNI => bni, Kartu Kredit => cc, Convenience Store (Alfamart/Indomaret) => cstore, COD => cod, Saldo iPaymu => member)
            #'pay_channel' => '', // Channel dari metode pembayaran, jika ada (Misal dari metode pembayaran Convenience Store => indomaret, alfamart)
            #'buyer_name'  => 'Agus', // Nama customer/pembeli(opsional) 
            #'buyer_phone' => '08123456789', // No HP customer/pembeli (opsional)
            #'buyer_email' => 'pembeli@mail.com', // Email customer/pembeli (opsional)
            /* ----------------------------------------------- */
            
            'format'   => 'json' // Format: xml atau json. Default: xml
            );
            
        $params_string = http_build_query($params);
        
        //open connection
        $ch = curl_init();
            
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($params));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            
        //execute post
        $request = curl_exec($ch);
           
        if ( $request === false ) {
            echo 'Curl Error: ' . curl_error($ch);
        } else {
            
            $resultIpaymu = json_decode($request, true);
             
            if( isset($resultIpaymu['url']) ){
                
                header('location: '. $resultIpaymu['url']);
            }else {
                echo "Error ". $resultIpaymu['Status'] .":". $resultIpaymu['Keterangan'];
            }
        }
            
        //close connection
        curl_close($ch);
        exit;
    }
 
 

}
