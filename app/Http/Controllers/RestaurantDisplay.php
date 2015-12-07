<?php

namespace App\Http\Controllers;


//use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Log;
use App\Http\Controllers\Controller;

//use Laravel\Lumen\Routing\Controller as BaseController;

class RestaurantDisplay extends Controller {
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


    public function getAllRestaurants($restaurant_id) {
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $restaurant_info = DB::table(config('db_table_names.restaurant_view'))
                            ->select('restaurant_id',config('db_table_names.restaurant_view').'.name','short_description', 'long_description', 'cuisines','profile_photo',
                                'avg_rating','review_count','max_package_price','min_package_price',
                                'min_order_value','min_order_count','total_orders','order_before','cancel_before',config('db_table_names.locality').'.name as locality_name',config('db_table_names.city').'.name as city_name')
                            ->join(config('db_table_names.locality'), config('db_table_names.locality').'.id','=',config('db_table_names.restaurant_view').'.locality_id')
                            ->join(config('db_table_names.city'), config('db_table_names.city').'.id','=',config('db_table_names.restaurant_view').'.city_id')
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


    public function getRestaurantSchedule($restaurant_id) {
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $restaurant_info = DB::table(config('db_table_names.restaurant_schedule'))
                            ->where('restaurant_id',$restaurant_id)
                            ->get();

            if (count($restaurant_info)) {
              //Check if the any data was matched.
              return response()->json($restaurant_info);   
            } 
            else {
                //Log error in case of empty query
                Log::error('Failed getRestaurantSchedule. No record found in restaurant_schedule table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
               
        }
        else {
            Log::error('Failed getRestaurantSchedule. resstaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }

    public function getRestaurantImages($restaurant_id) {
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $restaurant_info = DB::table(config('db_table_names.restaurant_images'))
                            ->where('restaurant_id',$restaurant_id)
                            ->get();

            if (count($restaurant_info)) {
              //Check if the any data was matched.
              return response()->json($restaurant_info);   
            } 
            else {
                //Log error in case of empty query
                Log::error('Failed getRestaurantSchedule. No record found in restaurant_images table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
               
        }
        else {
            Log::error('Failed getRestaurantSchedule. restaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }




    /*
    / Get the menu's associated with a particular restaurant. 
    / @param int $restaurant_id (passed in the url as config('globals.api_path')/{restaurant_id}/getRestaurantMenu/{Package_Type})
    / @param $package_type:'all' or 'package' or 'a-la-carte' depending on type of menu needed.
    / @return JSON array of menus with matching $restaurant_id
    */


    public function getRestaurantMenu($restaurant_id, $package_type='all') {
       
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {
            
            /*
            / Exact compares the "package_type". Need to create an enum for the same in the DB to ensure match
            */

            switch ($package_type) {
                case 'all': 
                    $menu = DB::table(config('db_table_names.menu'))
                                    ->where('restaurant_id',$restaurant_id)
                                    ->get();
                    break;

                case 'package': 
                    $menu = DB::table(config('db_table_names.menu'))
                                    ->where('restaurant_id',$restaurant_id)
                                    ->where('type','package')
                                    ->get();
                    break;

                case 'a-la-carte': 
                    $menu = DB::table(config('db_table_names.menu'))
                                    ->where('restaurant_id',$restaurant_id)
                                    ->where('type','a-la-carte')
                                    ->get();
                    break;
                
                default:
                    $menu = DB::table(config('db_table_names.menu'))
                                    ->where('restaurant_id',$restaurant_id)
                                    ->get();

            }

            if (count($menu)) {
                  //Check if any data was fetched.
                  return response()->json($menu);   
            } 
            else {
                //Log error in case an empty set was picked up.
                Log::error('Failed getRestaurantMenu. No record found in menu table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
            }
               
        }
        else {
            Log::error('Failed getRestaurantMenu. resstaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }
       
    }

   /*
    / Get the menu items associated with a particular menu. 
    / @param int $menu_id (passed in the url as config('globals.api_path')/{restaurant_id}/getMenuItem/{Menu_id})
    / @return JSON array of menus items with matching $menu_id
    */

    public function getMenuItem ($restaurant_id, $menu_id) {
        if (isset($menu_id) && is_numeric($menu_id)) {

            $menu_item = DB::table(config('db_table_names.menu_item'))
                            ->where('menu_id',$menu_id)
                            ->get();

            if (count($menu_item)) {
              //Check if the any data was matched.
              return response()->json($menu_item);   
            } 
            else {
                //Log error in case of empty query
                Log::error('Failed getMenuItem. No record found in restaurant_view table.',['menu_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
               
        }
        else {
            Log::error('Failed getMenuItem. menu_id is not set or not numeric.',['menu_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }

/*
    / Get the menu items options and there list associated with a particular menu item. 
    / @param int $menu_item_id (passed in the url as config('globals.api_path')/{restaurant_id}/getMenuItem/{Menu_id}/{menu_item_id})
    / @return JSON array of menus items options and the lists with matching $menu_item_id
    */

    public function getMenuItemOptionAndList ($restaurant_id, $menu_id, $menu_item_id) {
        if (isset($menu_item_id) && is_numeric($menu_item_id)) {

            $menu_item_options = DB::table(config('db_table_names.menu_item_option'))
                            ->select(config('db_table_names.menu_item_option').'.id as menu_item_option_id','menu_item_id',config('db_table_names.menu_item_option').'.name as menu_item_option_name','description','max_choice','min_choice','paid as isPaid','price',config('db_table_names.menu_item_option_list').'.name as menu_item_option_list_name',config('db_table_names.menu_item_option_list').'.id as menu_item_option_list_id')
                            ->join(config('db_table_names.menu_item_option_list'),config('db_table_names.menu_item_option').'.id','=', config('db_table_names.menu_item_option_list').'.menu_item_option_id')
                            ->where('menu_item_id',$menu_item_id)
                            ->get();

            if (count($menu_item_options)) {
              //Check if the any data was matched.
              return response()->json($menu_item_options);   
            } 
            else {
                //Log error in case of empty query
                Log::error('Failed getMenuItemOptionAndList. No record found in menu_item_option table.',['menu_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
               
        }
        else {
            Log::error('Failed getMenuItemOptionAndList. menu_item_id is not set or not numeric.',['menu_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }

}
