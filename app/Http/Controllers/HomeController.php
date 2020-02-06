<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Listing;
use App\Models\Widget;
use Location;
use Setting;
use MetaTag;
use LaravelLocalization;
use Theme;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function postIndex(Request $request) {
        $url = http_build_query($request->except('_token'));
        return redirect('/home?'.$url);
    }

    public function redirect(Request $request)
    {
        return redirect('/');
    }

    public function index(Request $request)
    {    
        if(!setting('custom_homepage')) {
              
            return app('App\Http\Controllers\BrowseController')->listings($request);
        }

        $current_locale = LaravelLocalization::getCurrentLocale();
        $data['widgets'] = Widget::where('locale', $current_locale)->orderBy('position', 'ASC')->get();
        $data['show_search'] = false;
        
        MetaTag::set('title', Setting::get('home_title'));
        MetaTag::set('description', 'Porttie is provides Bali Fast Track Airport Service, Lounge, transport, and tour. It&#039;s one stop solution for your travel needs in Bali.');
        MetaTag::set('keywords', Setting::get('site_keywords'));
        MetaTag::set('image', 'https://beta.porttie.com/storage/images/2020/01/09/00a27220adc5762631071847f7252672.jpg');
         

        return view('home.index', $data);
		
    }


    public function autocomplete(Request $request){
        if($request['category'] == 'all'){
            $data = Listing::select('title')
            ->orderBy('title', 'asc')  
            ->where('title', 'LIKE', '%'.$request['query'].'%') 
            ->where('is_published', 1) 
            ->whereNotNull('is_admin_verified')  
            ->inRandomOrder()->limit(8)->get();
        }else{
            $data = Listing::select('title')
            ->orderBy('title', 'asc')  
            ->where('title', 'LIKE', '%'.$request['query'].'%') 
            ->where('category_id', $request['category']) 
            ->where('is_published', 1) 
            ->whereNotNull('is_admin_verified')  
            ->limit(8)
            ->get();
        }
        

        return response()->json($data);
    }

}
