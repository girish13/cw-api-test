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



    public function update_filter_list($restaurant_id) {
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $filter_list = DB::table(config('db_table_names.filter'))
                            ->select('filter_list_id')
                            ->where('restaurant_id',$restaurant_id)
                            ->get();

            if (count($filter_list)) {
              //Check if the any data was matched.
                $result = array();
                foreach ($filter_list as $filter_value) {
                    array_push($result, (int)$filter_value->filter_list_id);
                }
                $result_unique=array_unique($result);
                asort($result_unique,1);
                DB::table(config('db_table_names.restaurant'))
                    ->where('id', $restaurant_id)
                    ->update(['filter_list' => implode(",",$result_unique)]);
                return 1;
            } 
            else {
                //Log error in case of empty query (no match found with current restaurant_id)
                Log::error('Failed  '.__METHOD__.'. No record found in restaurant table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
             
       
        }
        else {
            //Log error in case restaurant_id is blank or non-numeric
            Log::error('Failed '.__METHOD__.'. id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }


    public function update_cuisine_list($restaurant_id) {
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

     		//Scope does not permit access in nested DB Facade. Hence accessing it as GLOBALS
			if (!isset($GLOBALS['cuisine_update_rest_id'])) global $cuisine_update_rest_id; 
			$GLOBALS['cuisine_update_rest_id'] = $restaurant_id;


            $cuisine_list = DB::table(config('db_table_names.filter_list'))
                            ->select('name')
                            ->join(config('db_table_names.filter'), function ($join) {
								$join->on(config('db_table_names.filter').'.filter_list_id','=',config('db_table_names.filter_list').'.id')
									 ->where(config('db_table_names.filter').'.restaurant_id','=', $GLOBALS['cuisine_update_rest_id']);
								})
                            ->where('type','Cuisine')
                            ->get();
            if (count($cuisine_list)) {
              //Check if the any data was matched.
                $result = array();
                foreach ($cuisine_list as $filter_value) {
                    array_push($result, $filter_value->name);
                }
                $result_unique=array_unique($result);
                DB::table(config('db_table_names.restaurant'))
                    ->where('id', $restaurant_id)
                    ->update(['cuisines' => implode(", ",$result_unique)]);
            	return 1;
            } 
            else {
                //Log error in case of empty query (no match found with current restaurant_id)
                Log::error('Failed  '.__METHOD__.'. No record found in filter table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
             
       
        }
        else {
            //Log error in case restaurant_id is blank or non-numeric
            Log::error('Failed '.__METHOD__.'. id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }


    public function update_max_package_price($restaurant_id) {
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

        	//Scope does not permit access in nested DB Facade. Hence accessing it as GLOBALS
			if (!isset($GLOBALS['max_package_price_update_rest_id'])) global $max_package_price_update_rest_id; 
			$GLOBALS['max_package_price_update_rest_id'] = $restaurant_id;

            $max_price_list = DB::table(config('db_table_names.menu_package_price'))
                            ->select(DB::raw('max('.config('db_table_names.menu_package_price').'.price_per_person) as max_price'))
                            ->join(config('db_table_names.menu'), function ($join) {
								$join->on(config('db_table_names.menu').'.id','=',config('db_table_names.menu_package_price').'.menu_id')
									 ->where(config('db_table_names.menu').'.restaurant_id','=', $GLOBALS['max_package_price_update_rest_id']);
								})
                            ->get();
            if (count($max_price_list)) {
              //Check if the any data was matched.
                $result = array();
                foreach ($max_price_list as $price_value) {
                    $result = $price_value->max_price;
                }
                DB::table(config('db_table_names.restaurant'))
                    ->where('id', $restaurant_id)
                    ->update(['max_package_price' => $result]);
            	return 1;
            } 
            else {
                //Log error in case of empty query (no match found with current restaurant_id)
                Log::error('Failed  '.__METHOD__.'. No record found in menu_package_price table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
             
       
        }
        else {
            //Log error in case restaurant_id is blank or non-numeric
            Log::error('Failed '.__METHOD__.'. id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }


    public function update_min_package_price($restaurant_id) {
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

        	//Scope does not permit access in nested DB Facade. Hence accessing it as GLOBALS
			if (!isset($GLOBALS['min_package_price_update_rest_id'])) global $min_package_price_update_rest_id; 
			$GLOBALS['min_package_price_update_rest_id'] = $restaurant_id;

            $min_price_list = DB::table(config('db_table_names.menu_package_price'))
                            ->select(DB::raw('min('.config('db_table_names.menu_package_price').'.price_per_person) as min_price'))
                            ->join(config('db_table_names.menu'), function ($join) {
								$join->on(config('db_table_names.menu').'.id','=',config('db_table_names.menu_package_price').'.menu_id')
									 ->where(config('db_table_names.menu').'.restaurant_id','=', $GLOBALS['min_package_price_update_rest_id']);
								})
                            ->get();
            if (count($min_price_list)) {
              //Check if the any data was matched.
                $result = array();
                foreach ($min_price_list as $price_value) {
                    $result = $price_value->min_price;
                }
                DB::table(config('db_table_names.restaurant'))
                    ->where('id', $restaurant_id)
                    ->update(['min_package_price' => $result]);
            	return 1;
            } 
            else {
                //Log error in case of empty query (no match found with current restaurant_id)
                Log::error('Failed  '.__METHOD__.'. No record found in menu_package_price table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
             
       
        }
        else {
            //Log error in case restaurant_id is blank or non-numeric
            Log::error('Failed '.__METHOD__.'. id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }




//End of class
}