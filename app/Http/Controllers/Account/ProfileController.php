<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\UpdateUserProfile;
use Image;
use Storage;
use GeoIP;
use Mail;
use App\Models\Role;
class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
		$user = auth()->user();
		$countries = [null=> 'Country...'] + json_decode(file_get_contents(resource_path("data/country.json")), true);

		$lat = GeoIP::getLatitude();
		$lng = GeoIP::getLongitude();

		return view('account.profile', compact('user', 'countries', 'lat', 'lng'));
    }

    public function store(UpdateUserProfile $request)
    {
        $user = auth()->user();
        if($request->file('image')) {
            $image = Image::make($request->file('image'))
                    ->fit(300, 300, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->resizeCanvas(300, 300);
            Storage::cloud()->put('avatars/'.$user->path, (string) $image->encode());
            $user->avatar = Storage::cloud()->url("avatars/".$user->path);
            $user->save();
        }
        $user->fill($request->except('email'))->save();
		
		if($request->input('lat') && $request->input('lng')) {
            $lat = $request->input('lat');
            $lng = $request->input('lng');
            $user->location = \DB::raw("(GeomFromText('POINT($lat $lng)'))");
            $user->address = $request->input('location');
            if($request->input('country')) {
                $user->country = $request->input('country');
            }
            $user->save();
        }
		
		if($request->has('filters')) {
			foreach($request->input('filters') as $k => $v) {
				$user->filters[$k] = $v;
			}
			$user->save();
		}
		
		alert()->success(__('Successfully saved!'));
        return redirect(route('account.edit_profile.index'));
    }

    /**
     * Vendor the specified resource from storage.
     * @return Response
     */
    public function open_vendor()
    {
        $role = Role::find('6'); //vendor
        $user = auth()->user();
        if($role->getMeta('selectable')) {
            $user->assignRole($role);
            $user->save();
        }
        Mail::send([], [], function ($message)  use ($user) { 
            $message->to($user->email)
              ->subject('Success Join Merchant - porttie.com') 
              ->setBody('<h3>Hello '.$user->name.'</h3>
              <p>
              You are already a part of us. 
              This time you can promote your product at our place for free.</p>
              <p>
                  <a href="'.url('account/dashboard').'">Go To Dashboard</a>
              </p>
              <br>
              Thanks,<br><br>Porttie.com', 'text/html'); // for HTML rich messages
        });
        return view('account.vendor_true');
    }
}
