<?php

namespace App\Http\Controllers\Admin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Setting;
use App\DataTables\UsersDataTable;
use Kris\LaravelFormBuilder\FormBuilder;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = [];
        $data['users'] = user::orderBy('id')->get();
        dd($data);
        return view('panel::vendor.index', $data);
    }

    
}
