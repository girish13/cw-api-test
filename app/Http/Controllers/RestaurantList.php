<?php

namespace App\Http\Controllers;


//use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Log;
use App\Http\Controllers\Controller;

//use Laravel\Lumen\Routing\Controller as BaseController;

class RestaurantList extends Controller {

	/*
  / Restaurant List page controller
  */

    /*
    / Send the basic information of the Restaurant
    / @param int $restaurant_id passed in the url as config('globals.api_path')/{restaurant_id}/RestaurantDisplay
    / @return JSON of restaurant_view row ('restaurant_id','name','short_description','cuisines',
    /'profile_photo','avg_rating','review_count','max_package_price','min_package_price','min_order_value',
    /'min_order_count','total_order','order_before','cancel_before','locality_id','city_id','state_id') 
    / with matching $restaurant_id
    / (DOES NOT RETURN  ADDRESS FIELDS)
    */

    public function getRestaurantListView($restaurant_id) {
      
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $restaurant_info = DB::table(config('db_table_names.restaurant_view'))
                            ->select('restaurant_id',config('db_table_names.restaurant_view').'.name','short_description', 'long_description', 'cuisines','profile_photo',
                                'avg_rating','review_count','max_package_price','min_package_price',
                                'min_order_value','min_order_count','total_orders','order_before','cancel_before',config('db_table_names.locality').'.name as locality_name',config('db_table_names.city').'.name as city_name')
                            ->join(config('db_table_names.locality'), config('db_table_names.locality').'.id','=',config('db_table_names.restaurant_view').'.locality_id')
                            ->join(config('db_table_names.city'), config('db_table_names.city').'.id','=',config('db_table_names.restaurant_view').'.city_id')
                            ->where('restaurant_id',$restaurant_id)
                            ->get();

            if (count($restaurant_info)) {
              //Check if the any data was matched.
              return response()->json($restaurant_info);   
            } 
            else {
                //Log error in case of empty query
                Log::error('Failed getRestaurantListView. No record found in restaurant_view table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
               
        }
        else {
            Log::error('Failed getRestaurantListView. resstaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }



    public function getRestaurantList(Request $request) {
    	if ($request->isMethod('post')) {
    		if ($request->has('locality_id') && $request->has('date') && $request->has('time') && $request->has('pax')) {

    			$date=date_create_from_format('Y#m#d H#i', $request->input('date').' '.$request->input('time'));
          
          //$time=date_create_from_format('H#m',$request->input('time'));

          if (!isset($GLOBALS['requestDay'])) global $requestDay; //Scope does not permit access in nested DB Facade. Hence accessing it as GLOBALS
          $requestDay = $date->format('l');
          if (!isset($GLOBALS['requestDateTime'])) global $requestDateTime;
          $requestDateTime = $date->format('Y-m-d H:i:s');

          //get all locality_ids eligible for delivery in this area
    			$locality_id= $request->input('locality_id');
    			$locality_id_set = array();
    			$locality_id_data_set = DB::table(config('db_table_names.locality_serve'))
                            ->where('locality_id_1',$locality_id)
                            ->orWhere('locality_id_2',$locality_id)
                            ->get();

          foreach ($locality_id_data_set as $id) {
          	if ($id->locality_id_1 != $locality_id) array_push($locality_id_set, $id->locality_id_1);
          	else if ($id->locality_id_2 != $locality_id) array_push($locality_id_set, $id->locality_id_2);
        	}

          array_push($locality_id_set, (int)$locality_id); //type conversion to int to ensure uniformity in array elements. POST parameters are string by default
          array_unique($locality_id_set); //Not needed, but extra precautions.
          echo response()->json($locality_id_set); 
          //Generating the fetch restaurant query

          $restaurant_query=DB::table(config('db_table_names.restaurant_view'))
                               ->join(config('db_table_names.restaurant_schedule'), function ($join) {
                                  $join->on(config('db_table_names.restaurant_schedule').'.restaurant_id','=',config('db_table_names.restaurant_view').'.restaurant_id')
                                        ->where(config('db_table_names.restaurant_schedule').'.days','=', $GLOBALS['day'])
                                        ->where(config('db_table_names.restaurant_schedule').'.open','=','1');
                                        ->
                               })
                               ->where('locality_id',array_pop($locality_id_set));

          /*
          $restaurant_query = DB::table(config('db_table_names.restaurant_view'))
                              ->where('locality_id',array_pop($locality_id_set));

          if (count($locality_id_set)) {
              //Check if the any data was matched.
            
            foreach($locality_id_set as $locality_id) {
              $restaurant_query->orWhere('locality_id', $locality_id);
            }

    		  }*/

          $result=$restaurant_query->get();
          return response()->json($result);


        }
    	}

    }

}
