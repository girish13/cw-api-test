<?php

namespace App\Http\Controllers;


//use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Log;
use App\Http\Controllers\Controller;

//use Laravel\Lumen\Routing\Controller as BaseController;

class cwUtil extends Controller {

	/*
	/ Function to get possible list of order times
	*/

	public function getOrderTimeList() {

		$date_format='h:i A';
		$current_order_time= date_create_from_format($date_format,config('globals.first_order_time'));
		$last_order_time= date_create_from_format($date_format,config('globals.last_order_time'));
		$time_list=  array();

		if ($current_order_time>$last_order_time) {
			Log::error('Failed '.__METHOD__.'. First order time is greater than last order time.',['first_order_time'=>date_format($current_order_time,$date_format), 'last_order_time'=>$date_format($last_order_time,$date_format),]);
			return response()->json(['Error' => config('globals.error_msg')]);  
		}
		else {
			
			while ($current_order_time <= $last_order_time) {
				array_push($time_list,date_format($current_order_time,$date_format));
				date_modify($current_order_time,'+15 minutes');
			}

			return response()->json($time_list);
		}
	}

//End of class
}