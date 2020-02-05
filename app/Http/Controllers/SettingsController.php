<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('vendor.passport.settings');
    }

    public function oauth(Request $request )
    {
        $data = $request->all();
       
        return view('vendor.passport.authorize', $data);
    }
}
