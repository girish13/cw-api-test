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
    / Function get eligible localities for delivery radius.
    / @param - locality_id
    / @return - array of locality_ids
    */

    private function getDeliveryLocalities($locality_id) {
      //get all locality_ids eligible for delivery in this area
      

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
      return (array)$locality_id_set;
    }


    /*
    / Send the basic information of the Restaurant
    / @param int $restaurant_id passed in the url as config('globals.api_path')/{restaurant_id}/RestaurantDisplay
    / @return JSON of restaurant_view row ('restaurant_id','name','short_description','cuisines',
    /'profile_photo','avg_rating','review_count','max_package_price','min_package_price','min_order_value',
    /'min_order_count','total_order','order_before','cancel_before','locality_id','city_id','state_id') 
    / with matching $restaurant_id
    / (DOES NOT RETURN ADDRESS AND LONG DESCRIPTION FIELDS)
    */



    public function getRestaurantListView($restaurant_id) {
      
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $restaurant_info = DB::table(config('db_table_names.restaurant_view'))
                            ->select('restaurant_id',config('db_table_names.restaurant_view').'.name','short_description', 'cuisines','profile_photo',
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
            Log::error('Failed getRestaurantListView. restaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }


    public function getRestaurantListViewByPage(array $restaurant_id, $page='0', $sort='popularity') {
      
        $sort_var=0;
        $sort_order=0;

        switch($sort) {
          case 'popularity':
                $sort_var='total_orders';
                $sort_order='desc';
                break;
          case 'price-low':
                $sort_var='min_package_price';
                $sort_order='asc';
                break;
          case 'price-high':
                $sort_var='min_package_price';
                $sort_order='desc';
                break;
          case 'rating':
                $sort_var='avg_rating';
                $sort_order='desc';
                break;
          case default:
                $sort_var='total_orders';
                $sort_order='desc';
        }


        if (isset($restaurant_id) && count($restaurant_id)) {

            $restaurant_info = DB::table(config('db_table_names.restaurant_view'))
                            ->select('restaurant_id',config('db_table_names.restaurant_view').'.name','short_description', 'cuisines','profile_photo',
                                'avg_rating','review_count','max_package_price','min_package_price',
                                'min_order_value','min_order_count','total_orders','order_before','cancel_before',config('db_table_names.locality').'.name as locality_name',config('db_table_names.city').'.name as city_name')
                            ->join(config('db_table_names.locality'), config('db_table_names.locality').'.id','=',config('db_table_names.restaurant_view').'.locality_id')
                            ->join(config('db_table_names.city'), config('db_table_names.city').'.id','=',config('db_table_names.restaurant_view').'.city_id')
                            ->whereIn('restaurant_id',(array)$restaurant_id)
                            ->orderBy($sort_var,$sort_order)
                            ->skip(($page*10))
                            ->take(config('globals.list_page_size'))
                            ->get();

            if (count($restaurant_info)) {
              //Check if the any data was matched.
              return $restaurant_info;   
            } 
            else {
                //Log error in case of empty query
                Log::error('Failed getRestaurantListView. No record found in restaurant_view table.',['restaurant_id'=>$restaurant_id]);
                return false;    
            }
               
        }
        else {
            Log::error('Failed getRestaurantListView. restaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return false;          
        }

    }


    public function getRestaurantIdList(Request $request) {

  		if ($request->has('locality_id') && $request->has('date') && $request->has('time') && $request->has('pax')) {

  			$date = date_create_from_format('Y#m#d H#i', $request->input('date').' '.$request->input('time'));
        
        //Scope does not permit access in nested DB Facade. Hence accessing it as GLOBALS
        if (!isset($GLOBALS['requestDay'])) global $requestDay; 
        $requestDay = $date->format('l');
        
        $locality_id_set = $this->getDeliveryLocalities($request->input('locality_id'));
        
        if (count($locality_id_set)) {

          $restaurant_list=DB::table(config('db_table_names.restaurant_view'))
                               ->select(config('db_table_names.restaurant_view').'.restaurant_id')
                               ->join(config('db_table_names.restaurant_schedule'), function ($join) {
                                  $join->on(config('db_table_names.restaurant_schedule').'.restaurant_id','=',config('db_table_names.restaurant_view').'.restaurant_id')
                                        ->where(config('db_table_names.restaurant_schedule').'.days','=', $GLOBALS['requestDay'])
                                        ->where(config('db_table_names.restaurant_schedule').'.open','=','1');
                               })
                              ->whereIn('locality_id', $locality_id_set)
                              ->get();

          if (count($restaurant_list)) {
            
            $restaurant_id_set = array();
            foreach ($restaurant_list as $list) {
              array_push($restaurant_id_set, $list->restaurant_id);
            }
            
            if ($request->has('page') && is_numeric($request->input('page'))) {
              $restaurant_info = $this->getRestaurantListViewByPage($restaurant_id_set, $request->input('page'));
            }
            else {
               $restaurant_info = $this->getRestaurantListViewByPage($restaurant_id_set);
            }
            
            if (count($restaurant_info)) {
              return response()->json($restaurant_info);
            }
            else {
              Log::error('Failed '.__METHOD__.'. No record found in retaurant_view table for current search criteria.',['locality_id'=>$request->input('locality_id'), 'date'=>$request->input('date'), 'time'=>$request->input('time'), 'pax'=>$request->input('pax')]);
              return response()->json(['Error' => config('globals.error_msg')]);  
            }
          }
          else {
            Log::error('Failed '.__METHOD__.'. No record found in retaurant_view table for current search criteria.',['locality_id'=>$request->input('locality_id'), 'date'=>$request->input('date'), 'time'=>$request->input('time'), 'pax'=>$request->input('pax')]);
            return response()->json(['Error' => config('globals.error_msg')]);  
          }
          
        }
        else {
            Log::error('Failed '.__METHOD__.'. Error fetching locality delivery area',['locality_id'=>$request->input('locality_id'), 'date'=>$request->input('date'), 'time'=>$request->input('time'), 'pax'=>$request->input('pax')]);
            return response()->json(['Error' => config('globals.error_msg')]);  
        }
      }
      else {
            Log::error('Failed '.__METHOD__.'. Input not set. Either locality_id, date,time or pax missing. ',['locality_id'=>$request->input('locality_id'), 'date'=>$request->input('date'), 'time'=>$request->input('time'), 'pax'=>$request->input('pax')]);
            return response()->json(['Error' => config('globals.error_msg')]);  
      }
    	

    }

}
