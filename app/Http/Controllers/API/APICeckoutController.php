<?php

namespace App\Http\Controllers\API;

use App\Events\OrderPlaced;
use Illuminate\Http\Request;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
//use App\User; 

use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon; 
use Log;
use App\Models\CheckoutSession;
use App\Models\PaymentGateway;
use App\Models\PaymentProvider;
use App\Http\Requests\StoreCheckout;

use Illuminate\Routing\Controller;

class APICeckoutController extends Controller
{
    protected $category_id;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

	
    
	public function index($listing, Request $request)
    {
        
        #calculate the real price of the order
        $widget = '\App\Widgets\Order\\'.studly_case($listing->pricing_model->widget).'Widget';
        $widget = new $widget();
        $pricing = $widget->validate_payment($listing, $request);

        $data = [];
        $data['listing'] = $listing;
        $data['pricing'] = $pricing;

        $qs = (collect($request->except(['_token', '_method']))->reject(function ($value, $key) {
            return starts_with($key, 'ic-');
        }))->toArray();
        $qs['listing'] = $listing;
        $data['url'] = route('checkout', $qs);

        $data['user'] = User::with(['metas'])->find(auth()->user()->id);
        $data['billing_address'] = auth()->user()->billing_address;
        $data['shipping_address'] = auth()->user()->shipping_address;
        $data['countries'] = [null=> 'Country...'] + json_decode(file_get_contents(resource_path("data/country.json")), true);
#dd($listing->user_id);
        $data['payment_providers'] = PaymentProvider::with(['identifier' => function ($query) use($listing) {
			#dd($listing->user_id);
            $query->where('user_id', $listing->user_id);
        }])->where('is_enabled', 1)->orderBy('position', 'ASC')->get();
		
		$data['payment_providers'] = $data['payment_providers']->reject(function ($value) {
			return is_null($value->identifier);
		});
        #$data['payment_providers'] = PaymentProvider::has('identifier')->where('is_enabled', 1)->orderBy('position', 'DESC')->get();
        #dd($data['payment_providers']);
        if($data['payment_providers']) {
            if($request->has('provider')) {
                $data['payment_method'] = $request->input('provider');
            } else {
                if($data['payment_providers']->first()) {
                    $data['payment_method'] = $data['payment_providers']->first()->key;
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
	
    
    
    //checkout post
	public function store($listing, StoreCheckout $request) {
         
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];
 
        if (auth()->attempt($credentials)) {
            $user = auth()->user();
        } else {
            return response()->json(['error' => 'UnAuthorised'], 401);
        }
        
        #calculate the real price of the order
        $widget = '\App\Widgets\Order\\'.studly_case($listing->pricing_model->widget).'Widget';
        $widget = new $widget();
        $pricing = $widget->validate_payment($listing, $request);
         
        
        //$user['billing_address'] = $request->input('billing_address'); 
        $user->billing_address = $request->input('billing_address');
         
        if($request->has('same_address')) {
            $user->billing_address  = $request->input('billing_address'); 
            
        } else {
            $user->shipping_address  = $request->input('shipping_address');  
        }
        $user->save();
       
        //payment method
        $payment_provider = $request->input('payment_method'); //name : ipaymu
       
        $provider = PaymentProvider::where('key', $payment_provider)->first(); 

        $route = 'payments.'.str_slug($payment_provider).'.index';
        
        if($provider->is_offline) {
            $route = 'api.payments.offline.index';
        }
        if($provider->connection_url) {
            $route = 'api.payments.external.index';
        }
		
        $checkout_session = CheckoutSession::create([
            'listing_id'   =>  $listing->id,
            'user_id'   =>  $user->id,
            'request'   =>  $request->all(),
            'payment_provider_key'   =>  $payment_provider,
        ]);
        
        $checkout_session->extra_attributes->billing_address = $request->input('billing_address');
        $checkout_session->extra_attributes->shipping_address = $user->shipping_address;
        $checkout_session->save();

        $params = [];
        $params['session'] = $checkout_session;
        $params['key'] = $payment_provider; 
        

        if($payment_provider == 'ipaymu'){
            $ipayment_result = $this->ipaymu($listing, $pricing, $request, $checkout_session);

        }else{
            return response()->json([
                'success' => false,
                'data' => '',
                'error' => 'Your Payment Method not found !'
            ]);
        } 

        return response()->json([
            'success' => true,
            'data' => $ipayment_result 
        ]);
          
    }
    
    public function error_page(Request $request) {
        $data = [];
        $data['message'] = $request->input('message');
        return $data;
    }
	
	private function getCustomer($payment_gateway) {
        \Stripe\Stripe::setApiKey(config('marketplace.stripe_secret_key'));
        $customers = \Stripe\Customer::all([
            "limit" => 30,
            "email" => auth()->user()->email
		]);

        if( collect($customers->data)->count() ) {
            return collect($customers->data)->sortBy('created')->sortByDesc('subscriptions.total_count')->first()->id;
        }

        return false;
    }
	
	private function createOrUpdateCustomer($user, $token, $payment_gateway) {
        \Stripe\Stripe::setApiKey(config('marketplace.stripe_secret_key'));

		$membership_stripe_customer = $this->getCustomer($payment_gateway);

        if(!$membership_stripe_customer) {
            $customer = \Stripe\Customer::create([
                'email' => auth()->user()->email,
                'source' => $token,
			]);
            $user->membership_stripe_customer = $customer->id;
            $user->save();
        } else {

            $customer = \Stripe\Customer::retrieve($membership_stripe_customer);
            $customer->source = $token;
            $customer->save();

        }

        return $customer;
    }
	
     

    public function getContact($id, Request $request) {
        $listing = Listing::find($id);

        $data = [];
        $data['listing'] = $listing;
        return $data;
    }

    public function postContact($id, Request $request) {

        //send an email to the seller and add to his inbox
        $listing = Listing::findOrFail($id);
        $mail = Mail::to($listing->user)->send(new ListingContact($listing, $request->all()));
        return redirect('order/'.$id.'/contact');

    }

    public function ipaymu($listing, $pricing, $request, $checkout_session)
    {
        $payment_name = $request->input('payment_method'); 
         
        
        if($payment_name == 'ipaymu' && isset($listing->user_id)){
            $provider = PaymentGateway::where('user_id', $listing->user_id)->where('name', $payment_name)->first();
           
            $url_ipaymu = 'https://my.ipaymu.com/payment';  // URL Payment iPaymu   
            $unotify = route('payments.ipaymu.notify', ['session' => $checkout_session->id],true);
            $ucancel = route('payments.ipaymu.cancel', ['session' => $checkout_session->id],true);

            $params = array(   // Prepare Parameters            
                'key'      => $provider->metadata['ipaymu_kay_client'], // API Key Merchant / Penjual
                'action'   => 'payment',
                'product'  => array('Payment Product '.$listing->title),
                'price'    => array($pricing['total']), // Total Harga
                'quantity' => array(1),
                'comments' => 'Note : ', // Optional
                'ureturn'  => $provider->metadata['ipaymu_callback_client'].'/return.php?q=return_ipaymu',
                'unotify'  => $unotify,
                'ucancel'  => $ucancel,
             
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
