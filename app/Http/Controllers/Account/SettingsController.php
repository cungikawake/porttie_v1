<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('api.setting');
    }

    public function oauth(Request $request )
    {
        $data = $request->all();
       
        return view('vendor.passport.authorize', $data);
    }
}
