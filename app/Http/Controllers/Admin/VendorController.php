<?php

namespace App\Http\Controllers\Admin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Setting;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles; 
use Spatie\Permission\Models\Permission;
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
        $users = new User;
        if($request->get('q')) {
            $users = User::where('name', 'like', "%{$request->get('q')}%")
                        ->orWhere('email', 'like', "%{$request->get('q')}%");
        }

        $data['users'] = $users->orderBy('created_at', 'DESC')->role('Vendor')->where('verified', 1)->paginate(10);
        
        return view('panel::vendor.index', $data);
    }

    
}
