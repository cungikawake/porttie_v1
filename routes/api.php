<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
 

//get vendor detail
Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    //https://beta.porttie.com/api/auth/login
    Route::post('login', 'API\APIUserController@login');

    //https://beta.porttie.com/api/auth/register
	Route::post('register', 'API\APIUserController@register');
	 
	Route::middleware('auth:api')->group(function () {
        //https://beta.porttie.com/api/auth/user
	    Route::get('user', 'API\APIUserController@user');

        //https://beta.porttie.com/api/auth/logout
    	Route::get('logout', 'API\APIUserController@logout'); 
	});

});


//get vendor account
Route::group([
    'middleware' => 'api',
    'prefix' => 'account',
    'as' => 'account.', 
    'namespace' => 'Account'

], function ($router) { 
	Route::middleware('auth:api')->group(function () { 
	 	
		//get product vendor https://beta.porttie.com/api/account/listings
	    Route::resource('listings', 'ListingsController');

		//get product vendor https://beta.porttie.com/api/account/order
	    Route::resource('orders', 'OrdersController');
	});

});

//LISTINGS Shop
Route::group([
    'middleware' => 'api', 
    'prefix' => 'listing',
], function ($router) {

    Route::middleware('auth:api')->group(function () 
    {
        //get list all product
        //https://beta.porttie.com/api/listing/products
        //next page https://beta.porttie.com/api/listing/products?page=2
        Route::get('/products', 'API\APIListingsController@products');

        //Product Show 
        //https://beta.porttie.com/api/listing/{hash}/{slug}
        //contoh https://beta.porttie.com/api/listing/oz4YkwAYyr/parasailing-adventures-watersport-activity
        Route::get('/{listing}/{slug}', 'API\APIListingsController@index')->name('listing'); 


        Route::get('/checkout/{listing}', 'API\APIListingsController@checkout');
    });

});

//CHECKOUT shop
Route::group([
    'middleware' => 'api', 
    'prefix' => 'checkout',
], function ($router) {

    Route::middleware('auth:api')->group(function () 
    {
        //https://beta.porttie.com/api/checkout/oz4YkwAYyr
        Route::get('/{listing}', 'API\APICeckoutController@index');

    });

    //https://beta.porttie.com/api/checkout/oz4YkwAYyr
    //billing_address, shipping_address, payment_method
    Route::post('/{listing}', 'API\APICeckoutController@store');
});

//Payment shop
Route::group([
    'middleware' => 'api', 
    'prefix' => 'payments',
], function ($router) {
    Route::get('ipaymu/{checkout_session}', 'API\APIPaymentController@index');
}); 

    
 


 
