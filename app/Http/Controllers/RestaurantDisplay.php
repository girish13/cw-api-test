<?php

namespace App\Http\Controllers;


//use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Log;
use App\Http\Controllers\Controller;

//use Laravel\Lumen\Routing\Controller as BaseController;

class RestaurantDisplay extends Controller
{
    /*
    / Restaurant Display page controller
    */

    /*
    / Send the basic information of the Restaurant
    / @param int $restaurant_id passed in the url as config('globals.api_path')/{restaurant_id}/RestaurantDisplay
    / @return JSON of restaurant_view row ('restaurant_id','name','short_description','cuisines',
    /'profile_photo','avg_rating','review_count','max_package_price','min_package_price','min_order_value',
    /'min_order_count','total_order','order_before','cancel_before','locality_id','city_id','state_id') 
    / with matching $restaurant_id
    / (DOES NOT RETURN LONG_DESCRIPTION AND ADDRESS FIELDS)
    */

    public function getRestaurantListView($restaurant_id) {
    	
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $restaurant_info = DB::table('restaurant_view')
                            ->select('restaurant_id','name','short_description','cuisines','profile_photo',
                                'avg_rating','review_count','max_package_price','min_package_price',
                                'min_order_value','min_order_count','total_orders','order_before','cancel_before','locality_id','city_id','state_id')
                            ->where('restaurant_id',$restaurant_id)
                            ->get();

            if (count($restaurant_info)) {
              return response()->json($restaurant_info);   
            } 
            else {
            echo "in the error 1";
            Log::error('Failed getRestaurantListView. No record found in restaurant_view table.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => 'Internal Server Error. Please contact CaterWow customer support to report the error']);        
          }
               
        }
        else {
            echo "in the error 2";
            Log::error('Failed getRestaurantListView. resstaurant_id not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => 'Internal Server Error. Please contact CaterWow customer support to report the error']);            
        }

    }




}
