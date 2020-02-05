<?php

namespace App\Http\Controllers\API;

use App\Events\OrderPlaced;
use Illuminate\Http\Request;
use App\Models\Listing;
use App\Models\Order;
use App\User;
//use App\User; 

use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon; 
use Log;
use App\Models\CheckoutSession;
use App\Models\PaymentGateway;
use App\Models\PaymentProvider;
use App\Http\Requests\StoreCheckout;

use Illuminate\Routing\Controller;

class APIPaymentController extends Controller
{
     
	
    
	public function index($listing, Request $request)
    {
        $payment_name = $request->get('key');

        if($payment_name == 'ipaymu' && isset($listing->user_id)){
            $provider = PaymentGateway::where('user_id', $listing->user_id)->where('name', $payment_name)->first();
           
            $url_ipaymu = 'https://my.ipaymu.com/payment';  // URL Payment iPaymu   
                
            $params = array(   // Prepare Parameters            
                'key'      => $provider->metadata['ipaymu_kay_client'], // API Key Merchant / Penjual
                'action'   => 'payment',
                'product'  => array('Baju', 'Celana'),
                'price'    => array(1000, 15000), // Total Harga
                'quantity' => array(2, 1),
                'comments' => 'Keterangan Produk', // Optional
                'ureturn'  => $provider->metadata['ipaymu_callback_client'].'/return.php?q=return',
                'unotify'  => 'http://websiteanda.com/notify.php',
                'ucancel'  => 'http://websiteanda.com/cancel.php',
            
                /* Parameter tambahan untuk pembayaran COD
                * ----------------------------------------------- */
                'weight'     => array(1, 1), // Berat barang (satuan kilo, menerima array)
                'dimensi'    => array('1:2:1', '1:1:1'), // Dimensi barang (format => panjang:lebar:tinggi, menerima array)
                'postal_code'=> '80361',  // Kode pos untuk custom pikcup
                'address'    => 'Jalan Raya Kuta, No 88R, Badung, Bali', // Alamat untuk custom pickup
                /* ----------------------------------------------- */
            
                /* Parameter tambahan untuk custom payment page (hanya menampilkan satu metode pembayaran)
                * ----------------------------------------------- */
                'pay_method'  => 'arthagraha', // Metode pembayaran yang akan ditampilkan (VA BAG => arthagraha, VA Niaga => niaga, VA BNI => bni, Kartu Kredit => cc, Convenience Store (Alfamart/Indomaret) => cstore, COD => cod, Saldo iPaymu => member)
                'pay_channel' => '', // Channel dari metode pembayaran, jika ada (Misal dari metode pembayaran Convenience Store => indomaret, alfamart)
                'buyer_name'  => 'Agus', // Nama customer/pembeli(opsional) 
                'buyer_phone' => '08123456789', // No HP customer/pembeli (opsional)
                'buyer_email' => 'pembeli@mail.com', // Email customer/pembeli (opsional)
                /* ----------------------------------------------- */
            
                'format'   => 'json' // Format: xml atau json. Default: xml
                );
            
            $params_string = http_build_query($params);
            
            //open connection
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $url_ipaymu);
            curl_setopt($ch, CURLOPT_POST, count($params));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            
            //execute post
            $request = curl_exec($ch);
            
            if ( $request === false ) {
                echo 'Curl Error: ' . curl_error($ch);
            } else {
            
                return $result = json_decode($request, true);
                if( isset($result['url']) )
                    header('location: '. $result['url']);
                else {
                    echo "Error ". $result['Status'] .":". $result['Keterangan'];
                }
            }
            
            //close connection
            curl_close($ch);
            exit;
        }    
    }
	
     
}
