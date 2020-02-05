<?php

namespace App\Http\Controllers\API;

use App\Mail\ListingVerified;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use App\Models\Filter;
use App\Models\Listing;
use App\Models\Category;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use View;
use Location;
use Mapper;
use Setting;
use MetaTag;
use Mail;
use App\User; 
use Illuminate\Support\Facades\Auth; 


use App\Models\ListingBookedTime;
use Arrilot\Widgets\AbstractWidget; 
use Applicazza\Appointed\BusinessDay;
use Applicazza\Appointed\Period;
use Applicazza\Appointed\Appointment;
use Applicazza\Appointed;
use VM\TimeOverlapCalculator\Entity\TimeSlot;
use VM\TimeOverlapCalculator\Generator\TimeSlotGenerator;
use VM\TimeOverlapCalculator\TimeOverlapCalculator;
use App\Models\ListingBookedDate;
use App\Models\ListingProductClose;

use Carbon\Carbon;

class APIListingsController extends Controller
{
	public $successStatus = 200;
    protected $category_id;
    protected $config = [];

    /**
     * Display a listing of the resource.
     * @return Response
     */

    public function products(){

    	$listings = Listing::where('user_id', auth()->user()->id)->orderBy('created_at', 'DESC')->paginate(2);

        return response()->json([
            'success' => true,
            'data' => $listings
        ]);
    }

    public function index($listing, $slug, Request $request)
    {
        
        $data = [];
        $visible_listing = $listing->is_published && $listing->is_admin_verified && !$listing->is_disabled;
        $can_edit = auth()->check() && (auth()->user()->id == $listing->user_id || auth()->user()->can('edit listing'));

        if(!$visible_listing && !$can_edit) {
            return abort(404);
        }

		$breadcrumbs = [];
        $category = $listing->category;
        
		if($category) {
			$i = 0;
			while($category = $category->child) {
				$breadcrumbs[] = $category;
				$i ++;
				if($i == 5)
					break;
			}
		}
        $data['breadcrumbs'] = array_reverse($breadcrumbs);
          
        $data['has_map'] = false;
        if($listing->location && setting('googlmapper.key')) {
            Mapper::map($listing->location->getLat(), $listing->location->getLng(), ['zoom' => 14, 'zoomControl' => false, 'streetViewControl' => false, 'mapTypeControl' => false, 'center' => true, 'marker' => true]);
            $data['has_map'] = true;
        }
         
		$listing->load('shipping_options');
		$listing->load('additional_options'); 
        $data['listing'] = $listing;
        
        $data['seller'] = $listing->user;
        $data['filters'] = Filter::orderBy('position', 'ASC')->get();
        
        #$data['comments'] = $listing->comments()->orderBy('created_at', 'DESC')->limit(5)->get();
        #$data['comment_count'] = $listing->totalCommentCount();
        
        MetaTag::set('title', $listing->title);
        MetaTag::set('description', $listing->description); 
        MetaTag::set('image', url($listing->thumbnail));
        
         
        if($data['listing']->pricing_model->widget == 'book_time'){
            $result_calc = $this->run_book_time($listing); 

        }else if($data['listing']->pricing_model->widget == 'book_date'){
            $result_calc = $this->run_book_date($listing); 
        }

        return response()->json([
            'success' => true,
            'data' => $result_calc
        ]);
    }

    /**
     * The configuration widget BOOK TIME
     *
     *  
     */

    public function calculate_price_book_time($listing, $params)
    {
         
        $fee_percentage = setting('marketplace_percentage_fee');
        $fee_transaction = setting('marketplace_transaction_fee');

        $start_date = isset($params['start_date']) ? $params['start_date'] : null;
        $selected_slot = isset($params['slot']) ? $params['slot'] : null;
        $quantity = isset($params['quantity']) ? $params['quantity'] : 1;
		$additional_options = isset($params['additional_option'])?$params['additional_option']:[];
        $additional_options_meta = isset($params['additional_options_meta'])?$params['additional_options_meta']:[];
        $variants = isset($params['variant'])?$params['variant']:null;
        $guest_name = isset($params['guest_name'])?$params['guest_name']:null;
        $guest_card = isset($params['guest_card'])?$params['guest_card']:null;
        $guest_flight = isset($params['guest_flight'])?$params['guest_flight']:null;
        $guest_note = isset($params['guest_note'])?$params['guest_note']:null;

        $listing_price = $listing->price;

        #calculate additional variant cost
		$selected_variant = null;

        $error = false;
        $selected_services = false;
        $user_choice = [];
        $user_choice[] = ['group' => 'general', 'name' => 'Quantity', 'value' => $quantity.' '.$listing->pricing_model->duration_name];
        $timeslots = [];

        #start get price variant
        $variant_stock = 0;
        if($variants) {
            $listing_price = 0;
			$variant_pricing = $listing->variants;
			foreach($variants as $k => $v) {
				$variant_pricing = $variant_pricing->where("meta.$k", $v);
                $user_choice[] = ['group' => 'variant', 'name' => $k, 'value' => $v];
            }
			
			if($variant_pricing->count() == 1) {
                $selected_variant = $variant_pricing->first();
                $variant_stock = $selected_variant->stock;
				$listing_price += $selected_variant->price;
				if($quantity > $selected_variant->stock) {
					$error = __('Insufficient stock. Please lower the quantity.');
				}				
				if($selected_variant->stock < 1) {
					$error = __('Out of Stock');
				}
			}
        }
        #end variant price
        
        $total_price = $listing_price;
        $session_length = (int) $listing->session_duration;
        if($listing->services && isset($params['service'])) {
            $params['service'] = array_keys($params['service']);
            $selected_services = $listing->services->whereIn('id', $params['service']);
            $total_price = $selected_services->sum('price');
            $session_length = $selected_services->sum('duration');
        }

		#additional pricing
        $additional_options_price = $listing->additional_options->reduce(function ($carry, $item) use($additional_options, $additional_options_meta) {
            /*if(in_array($item->id, array_keys($additional_options)))
                return $carry + $item->price;
            return $carry;
            */
            if(in_array($item->id, array_keys($additional_options))) {
                $price = $item->price;
                $quantity = 1;

                if(in_array($item->id, array_keys($additional_options_meta)) && isset($additional_options_meta[$item->id]['quantity'])) {               
                    $quantity = (int) $additional_options_meta[$item->id]['quantity'];
                }               
                return $carry + ($price*$quantity);
            }
                        
            return $carry;
        }, 0);



		$number = 0;
		foreach($listing->additional_options as $k => $item) {
            if(in_array($item->id, array_keys($additional_options))) {
                $number++;
                $quantity = 1;
                if(in_array($item->id, array_keys($additional_options_meta)) && isset($additional_options_meta[$item->id]['quantity'])) {               
                    $quantity = (int) $additional_options_meta[$item->id]['quantity'];
                } 
                $user_choice[] = ['group' => 'additional_options', 'name' => '* '.$item->name, 'value' => $quantity, 'price' => $item->price];
            }
        }

        //date, time, qty
        $subtotal = ($quantity * $total_price) + $additional_options_price;
        $service_fee_percentage = $subtotal * ($fee_percentage/100);
        
        if($subtotal == 0)
            $fee_transaction = 0;
        $service_fee_transaction = $fee_transaction;
        $service_fee = $service_fee_percentage + $service_fee_transaction;
        $total = $subtotal + $service_fee;
        

        $timeSlotGenerator = new TimeSlotGenerator();
        $calculator = new TimeOverlapCalculator();
        $time_slots = [];
        if ($start_date) {
            $start_date = Carbon::createFromFormat('d-m-Y', $start_date);

            $day_of_week = $start_date->format('N');
 
            if ($listing->timeslots) {
                foreach ($listing->timeslots as $timeslot) {
                    if ($day_of_week == $timeslot['day']) {

                        $start_time = (int) $timeslot['start_time'];
                        $end_time = (int) $timeslot['end_time'];

                        $time_slots[] = new TimeSlot(\Applicazza\Appointed\today( $start_time, 0), \Applicazza\Appointed\today($end_time, 0));
                    }
                }
            }
        }

        $free_time_slots = $calculator->mergeOverlappedTimeSlots(
            $timeSlotGenerator,
            $time_slots
        );

        $business_day = new BusinessDay;
        foreach ($free_time_slots as $timeslot) {
            $period = Period::make($timeslot->getStart(), $timeslot->getEnd());
            $business_day->addOperatingPeriods(
                $period
            );
        }

        //add booked appointments
        if($start_date) {
            $booked_slots = ListingBookedTime::where('listing_id', $listing->id)->where('booked_date', $start_date->toDateString())->get();
            foreach ($booked_slots as $booked_slot) {
                if ($booked_slot->quantity >= $variant_stock) {
                    $appointment = Appointment::make(Carbon::parse($booked_slot->start_time), Carbon::parse($booked_slot->start_time)->addMinutes($booked_slot->duration));
                    $business_day->addAppointments($appointment);
                }
            }
        }

        //now generate some time slots to choose from
        $slot_interval = 15;
		if($listing->session_duration)
			$slot_interval = $listing->session_duration;
		 
        $available_slots = [];
        $agenda = $business_day->getAgenda();

        foreach($agenda as $timeslot) {
            if($timeslot->jsonSerialize()['status'] == "available") {
                $period = new \League\Period\Period($timeslot->getStartsAt(), $timeslot->getEndsAt());
                foreach ($period->split($slot_interval . ' minutes') as $period) {
                    $available_slots[] = $period->getStartDate()->format('H:i');
                }
            }
        }
        $timeslots = $available_slots;
        if (!$error && count($available_slots) == 0 && $start_date) {
            $error = 'Sorry, no available time slots. Try another date.';
        }

        if($start_date && $selected_slot && !in_array($selected_slot, $available_slots)) {
            $error = 'Invalid slot.';
        }
 
		$price_items = [
			[
				'key' 	=> 'price',
				'label' => __('Subtotal'),
				'price' => $subtotal
			],
			[
				'key'	=> 'service',
				'label' => __('Service fee'),
				'price' => $service_fee,
				'notice' => __('This fee helps cover the costs of operating the website'),
			],
		]; 

        if($start_date) {
            $user_choice[] = ['group' => 'dates', 'name' => 'Booking day', 'value' => $start_date->toFormattedDateString()];
            
            $user_choice[] = ['group' => 'times', 'name' => 'Booking time', 'value' => $selected_slot];

            $user_choice[] = ['group' => 'guest_info', 'name' => 'Guest name', 'value' => $guest_name];
            $user_choice[] = ['group' => 'guest_info', 'name' => 'Guest card', 'value' => $guest_card];
            $user_choice[] = ['group' => 'guest_info', 'name' => 'Flight number', 'value' => $guest_flight];
            $user_choice[] = ['group' => 'guest_info', 'name' => 'Note', 'value' => $guest_note];
            
            if(isset($selected_services) && $selected_services) {
                foreach($selected_services as $selected_service) {
                    $user_choice[] = ['group' => 'services', 'name' => 'Service', 'value' => $selected_service['name']];
                }
            }
        } 
        
		return [
            'user_choice'	=>	$user_choice,
            'error'			=>	$error,
			'total'			=>	$total,
			'duration'		=>	$session_length,
			'service_fee'	=>	$service_fee,
			'price_items'	=>	$price_items,
            'timeslots'	    =>	$timeslots,
            'guest_name'    =>  $guest_name,
            'guest_card'    =>  $guest_card,
            'guest_flight'    =>  $guest_flight,
            'guest_note'    =>  $guest_note,
		];
	
	}

    public function decrease_stock($order, $listing)
    {
        //add quantity to the listing_booked_dates table
        $booked_date = Carbon::createFromFormat('d-m-Y', $order->listing_options['start_date']);
        $slot = $order->listing_options['slot'];
        $duration = $order->listing_options['duration'];

        $booked_slot = ListingBookedTime::firstOrCreate([
            'listing_id' => $order->listing->id,
            'booked_date' => $booked_date->toDateString(),
            'start_time' => $slot,
            'duration' => $duration,
        ], ['quantity' => 0]);

        $booked_slot->increment('quantity', $order->listing_options['quantity']);

    }

    public function validate_payment($listing, $request)
    {
		return $this->calculate_price($listing, $request);
	}
	
    private function getDisabledDates($listing) {
	
		#find availability for next 3 months
		$start = Carbon::now()->startOfDay();
        $end = Carbon::now()->addMonths(3)->endOfDay();
		$weekdays = [];
		if($listing->timeslots && is_array($listing->timeslots)) {
			$weekdays = array_values(collect($listing->timeslots)->pluck('day')->unique()->toArray());
		}
		$disabled_dates = [];
		for($date = $start; $date->lte($end); $date->addDay()) {
		    $day_of_week = $date->format('N');
			if (!in_array($day_of_week, $weekdays)) {
				$disabled_dates[] = $date->format('d-m-Y');
			}
		}
		
		return ['weekdays' => $weekdays, 'disabled_dates' => $disabled_dates];
    }
    
    public function run_book_time($listing)
    {
        //
		$selected_slot = request('slot');
		$start_date = request('start_date');
		$error = null;
		if($start_date) {
			try {
				$start_date = Carbon::createFromFormat('d-m-Y', $start_date);
			} catch (\Exception $e) {
				$start_date = null;
				if(request('start_date'))
					$error = __('Invalid date');
			}
		}

		$quantity = 1;
		$price_items = [];
		$total = 0;
		$timeslots = [];
        $guest_name = '';
        $guest_card = '';
        $guest_flight = '';
        $guest_note = '';
        $duration = 0;
		#$result = $this->calculate_price($listing);
        #dd(request()->all());
        $result = $this->calculate_price_book_time($listing, request()->all());

        if($result) {
			$price_items = $result['price_items'];
			$total = $result['total'];
			$timeslots = $result['timeslots'];
            $duration = $result['duration'];
            $guest_name = $result['guest_name'];
            $guest_card = $result['guest_card'];
            $guest_flight = $result['guest_flight'];
            $guest_note = $result['guest_note'];
			if($result['error'])
				$error = $result['error'];
		}
		
		$disabled_dates = $this->getDisabledDates($listing);
        #dd($disabled_dates);
        return  $data = [
            'config' => $this->config,
            'qs' 	        => http_build_query(request()->all()),
            
            'error' => $error,
            'listing' => $listing,
            'booked_dates' => $disabled_dates['disabled_dates'],
            'selected_slot' => $selected_slot,
            'duration' => $duration,
            'start_date' => $start_date,
            'timeslots' => $timeslots,
			'weekdays' => $disabled_dates['weekdays'], 
            'quantity' => $quantity,
            'price_items' => $price_items,
            'total' => $total,
            'guest_name' => $guest_name,
            'guest_card' => $guest_card,
            'guest_flight'    =>  $guest_flight,
            'guest_note'    =>  $guest_note, 
            'user_choice' => $result['user_choice']
        ];
    }


    /**
     *  The configuration widget BOOK DATE
     *  
     */

    public function calculate_price_book_date($listing, $params) {

        $fee_percentage = setting('marketplace_percentage_fee');
        $fee_transaction = setting('marketplace_transaction_fee');

        $start_date = isset($params['start_date'])?$params['start_date']:null;
        $end_date = isset($params['end_date'])?$params['end_date']:null;
        $quantity = isset($params['quantity'])?$params['quantity']:1;
        $additional_options = isset($params['additional_option'])?$params['additional_option']:[];
        $additional_options_meta = isset($params['additional_options_meta'])?$params['additional_options_meta']:[];
        $variants = isset($params['variant'])?$params['variant']:null;
        $guest_name = isset($params['guest_name'])?$params['guest_name']:null;
        $guest_card = isset($params['guest_card'])?$params['guest_card']:null;
        $guest_flight = isset($params['guest_flight'])?$params['guest_flight']:null;
        $guest_note = isset($params['guest_note'])?$params['guest_note']:null;

        $listing_price = $listing->price;

        #calculate additional variant cost
		$selected_variant = null;

        $error = null;
        $user_choice = [];
        $user_choice[] = ['group' => 'general', 'name' => 'quantity', 'value' => $quantity.' '.$listing->pricing_model->duration_name];

        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);

        $units = $end_date->diffInDays($start_date);
        if($listing->pricing_model->duration_name == 'day' && $start_date) {
            $units++;
        }

        #start get price variant
        $variant_stock = 0;
        if($variants) {
            $listing_price = 0;
			$variant_pricing = $listing->variants;
			foreach($variants as $k => $v) {
				$variant_pricing = $variant_pricing->where("meta.$k", $v);
                $user_choice[] = ['group' => 'variant', 'name' => $k, 'value' => $v];
            }
			
			if($variant_pricing->count() == 1) {
                $selected_variant = $variant_pricing->first();
                $variant_stock = $selected_variant->stock;
				$listing_price += $selected_variant->price;
               
                if($quantity > $selected_variant->stock) {
					$error = __('Insufficient stock. Please lower the quantity.');
                }	
                			
				if($selected_variant->stock < 1) {
					$error = __('Out of Stock');
				}
			}
        }
        #end variant price 
        

		#start additional pricing
		$additional_options_price = $listing->additional_options->reduce(function ($carry, $item) use($additional_options, $additional_options_meta) {
            if(in_array($item->id, array_keys($additional_options))) {
				$price = $item->price;
				$quantity = 1;
				if(in_array($item->id, array_keys($additional_options_meta)) && isset($additional_options_meta[$item->id]['quantity'])) {				
					$quantity = (int) $additional_options_meta[$item->id]['quantity'];
				}				
                return $carry + ($price*$quantity);
			}
						
            return $carry;
        }, 0);

        $number = 0;
        foreach($listing->additional_options as $k => $item) {
            if(in_array($item->id, array_keys($additional_options))) {
                $number++;
                $user_choice[] = ['group' => 'additional_options', 'name' => 'Option '.($k+1), 'value' => $item->name, 'price' => $item->price];
            }
        }
        #end additional pricing

        #calculate all component
        $subtotal = $units * $listing_price * $quantity;
		$subtotal = $subtotal + $additional_options_price;
        $service_fee_percentage = $subtotal * ($fee_percentage/100);
        $service_fee_transaction = $fee_transaction;
        $service_fee = $service_fee_percentage + $service_fee_transaction;
        $total = $subtotal + $service_fee;
        

        $price_items = [];
		if($quantity == 1) {
			$unit_price = $units * $listing_price * $quantity;
			$price_items[] = [
				'label' => __(':price x :units :unit_label', ['price' => $listing_price, 'units' => $units, 'unit_label' => _p($listing->pricing_model->duration_name, $units)]), 'price' => $unit_price
			];
		} else {
			$unit_price = $units * $listing_price * $quantity;
			$price_items[] = [
				'label' => __(':price x :units :unit_label x:qty', ['price' => $listing_price, 'units' => $units, 'unit_label' => _p($listing->pricing_model->duration_name, $units), 'qty' => $quantity]), 'price' => $unit_price
            ];
		}
        
		if($additional_options_price) {
            $price_items[] = [
                'key'	=> 'additional',
                'label'	=> __('Additional options'),
                'price'	=> $additional_options_price,
            ];
        }
		
		if($service_fee > 0) {
			$price_items[] = [
				'label' => __('Service fee'),
				'price' => $service_fee,
				'notice' => __('This fee helps cover the costs of operating the website'),
			];
        }
		

        if($units < $listing->min_duration) {
            $error = __('Please select at least :days day(s)/night(s)', ['days' => $listing->min_duration]);
        }

		if($start_date->isPast() || $end_date->isPast()) {
            $error = __('These dates cannot be booked.');
        }

        #check if we have enough "stock" rooms for the day
        for($booked_date = $start_date->copy(); $booked_date->lte($end_date); $booked_date->addDay()) {
            
            $booked_day = ListingBookedDate::where('listing_id', $listing->id)->where('booked_date', $booked_date->toDateString())->first();
            
            if ($booked_day) {
                $remaining = $variant_stock - $booked_day->quantity;
                #check sisa stok per hari
                if ($quantity > $remaining) {
                    $error = __('Sorry no availability. Please try a different date range.');
                    break;

                }else{
                    #check hari libur
                    $booked_off = ListingProductClose::where('listing_id', $listing->id)->where('booked_date', $booked_date->toDateString())->first();

                    if($booked_off){
                        $error = __('Sorry no availability. Please try a different date range.');
                        break;
                    }
                }
            }
        }

        if($start_date && $end_date) {
            $user_choice[] = ['group' => 'dates', 'name' => 'Start day', 'value' => $start_date->toFormattedDateString()];
            $user_choice[] = ['group' => 'dates', 'name' => 'End day', 'value' => $end_date->toFormattedDateString()];
            $user_choice[] = ['group' => 'guest_info', 'name' => 'Guest name', 'value' => $guest_name];
            $user_choice[] = ['group' => 'guest_info', 'name' => 'Guest card', 'value' => $guest_card];
            $user_choice[] = ['group' => 'guest_info', 'name' => 'Flight number', 'value' => $guest_flight];
            $user_choice[] = ['group' => 'guest_info', 'name' => 'Note', 'value' => $guest_note];
        } 

        return [
            'user_choice'	=>	$user_choice,
            'error'			=>	$error,
            'total'			=>	$total,
            'units'	        =>	$units,
            'service_fee'	=>	$service_fee,
            'price_items'	=>	$price_items,
            'guest_name'    =>  $guest_name,
            'guest_card'    =>  $guest_card,
            'guest_flight'    =>  $guest_flight,
            'guest_note'    =>  $guest_note,
        ];

    } 
    
    public function run_book_date($listing)
    {
        
        //show detail product for guest
        $result = $this->calculate_price_book_date($listing, request()->all());

        //we also need to figure out what dates are fully taken - in the next 3 months?
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->addMonths(3)->endOfDay();
		#dev_dd($listing->stock);
        $booked_dates = ListingBookedDate::where('listing_id', $listing->id)
                                    ->whereBetween('booked_date', [$start, $end])
                                    ->where('quantity', '>=', (int) $listing->stock)
                                    ->get()
                                    ->pluck('booked_date_string');
       
        return $data = [
            'config' => $this->config,
            'qs' 	        => http_build_query(request()->all()),
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
            'booked_dates' => $booked_dates,
            'listing' => $listing,
            'units' => $result['units'],
            'service_fee' 	=> $result['service_fee'],
            'price_items' 	=> $result['price_items'],
            'total' 	=> $result['total'],
            'error' 	=> $result['error'],
            'guest_name' => $result['guest_name'],
            'guest_card' => $result['guest_card'],
            'guest_flight'    =>  $result['guest_flight'],
            'guest_note'    =>  $result['guest_note'],
        ];
    }
    
     
	 
}
