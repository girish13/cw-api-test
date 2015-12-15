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
    / Check if a menu belong to a particular restaurant
    / @param: INT $resturant_id, INT $menu_id
    / return BOOLEAN: true in case the menu belongs to restaurant, else false. 
    */

    private function checkMenuBelongsRestaurant($restaurant_id, $menu_id) {

        $menu_check = DB::table(config('db_table_names.menu'))
                                ->where('id','=',$menu_id)
                                ->where('restaurant_id','=',$restaurant_id)
                                ->get();

        return (count($menu_check)>0? true : false);        

    }

    
    /*
    / Check if a menu_item belong to a particular menu
    / @param: INT $menu_id, INT $menu_item_id
    / return BOOLEAN: true in case the menu_item belongs to restaurant, else false.
    */

    private function checkMenuItemBelongsMenu($menu_id, $menu_item_id) {

        $menu_item_check = DB::table(config('db_table_names.menu_item'))
                        ->where('id','=',$menu_item_id)
                        ->where('menu_id','=',$menu_id)
                        ->get();

        return (count($menu_item_check)>0? true : false);      

    }


   
    /*
    / Send the basic information of the Restaurant (restaurant_id)
    / @param int $restaurant_id
    / @return JSON of restaurant_view row
    / 
    */

    public function getRestaurantInfo($restaurant_id) {
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $restaurant_info = DB::table(config('db_table_names.restaurant_view'))
                            ->select('restaurant_id',config('db_table_names.restaurant_view').'.name','short_description', 'long_description', 'cuisines','profile_photo',
                                'avg_rating','review_count','max_package_price','min_package_price', 'min_order_value','min_order_count','total_orders',
                                'order_before','cancel_before',config('db_table_names.locality').'.name as locality_name',config('db_table_names.city').'.name as city_name')
                            ->join(config('db_table_names.locality'), config('db_table_names.locality').'.id','=',config('db_table_names.restaurant_view').'.locality_id')
                            ->join(config('db_table_names.city'), config('db_table_names.city').'.id','=',config('db_table_names.restaurant_view').'.city_id')
                            ->where(config('db_table_names.restaurant_view').'.restaurant_id','=',$restaurant_id)
                            ->get();

            if (count($restaurant_info)) {
              //Check if the any data was matched.
              return response()->json($restaurant_info);   
            } 
            else {
                //Log error in case of empty query(no match found with current restaurant_id)
                Log::error('Failed '.__METHOD__.'. No record found in restaurant_view table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
               
        }
        else {
            //Log error in case restaurant_id is blank or non-numeric
            Log::error('Failed '.__METHOD__.'. Restaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }

   
    /*
    / Send the day wise schedule of the Restaurant (restaurant_id)
    / @param int $restaurant_id
    / @return JSON of scheule row(s) - One row per day of the week with 'open' flag, 'open_time','close_time'
    / NOTE: 'day' defined as enum: 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'
    */

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
                //Log error in case of empty query (no match found with current restaurant_id)
                Log::error('Failed '.__METHOD__.'. No record found in restaurant_schedule table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
               
        }
        else {
            //Log error in case restaurant_id is blank or non-numeric
            Log::error('Failed '.__METHOD__.'. restaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }

   
    /*
    / Send an array of relative URI of  images  for the given Restaurant (restaurant_id)
    / @param int $restaurant_id
    / @return JSON of image ABSOLUTE URI(s) with Title and description
    */

    public function getRestaurantImages($restaurant_id) {
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $restaurant_info = DB::table(config('db_table_names.restaurant_images'))
                            ->select(DB::raw('id, restaurant_id, title, description, concat(\''.config('globals.storage_endpoint').'\',photo_address) as photo_address'))
                            ->where('restaurant_id',$restaurant_id)
                            ->get();

            if (count($restaurant_info)) {
              //Check if the any data was matched.
                
                /*foreach ($restaurant_info as $info_item) {
                    //Append storage end point to each image
                    $info_item['photo_address'] = config('globals.storage_endpoint').$info_item['photo_address'];
                }
                */

                return response()->json($restaurant_info);   
            } 
            else {
                //Log error in case of empty query (no match found with current restaurant_id)
                Log::error('Failed  '.__METHOD__.'. No record found in restaurant_images table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
             
       
        }
        else {
            //Log error in case restaurant_id is blank or non-numeric
            Log::error('Failed '.__METHOD__.'. restaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }


    /*
    / Send an array reviews and ratings  for the given Restaurant (restaurant_id)
    / @param int $restaurant_id
    / @return JSON of restaurant reviews along with customer name, customer locality name and customer customer city name
    */

    public function getRestaurantReview($restaurant_id) {
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $restaurant_info = DB::table(config('db_table_names.restaurant_review'))
                            ->select(config('db_table_names.restaurant_review').'.id', config('db_table_names.restaurant_review').'.restaurant_id', 
                                config('db_table_names.restaurant_review').'.customer_id', config('db_table_names.restaurant_review').'.rating',
                                config('db_table_names.restaurant_review').'.title', config('db_table_names.restaurant_review').'.body',
                                config('db_table_names.customer').'.fname as customer_fname',config('db_table_names.customer').'.lname as cusomer_lname',
                                config('db_table_names.locality').'.name as locality_name',config('db_table_names.city').'.name as city_name', 
                                config('db_table_names.locality').'.id as locality_id', config('db_table_names.city').'.id as city_id')
                            ->join(config('db_table_names.customer'), config('db_table_names.customer').'.id','=',config('db_table_names.restaurant_review').'.customer_id')
                            ->join(config('db_table_names.locality'), config('db_table_names.locality').'.id','=',config('db_table_names.customer').'.locality_id')
                            ->join(config('db_table_names.city'), config('db_table_names.city').'.id','=',config('db_table_names.customer').'.city_id')
                            ->where(config('db_table_names.restaurant_review').'.restaurant_id',$restaurant_id)
                            ->where(config('db_table_names.restaurant_review').'.title','!=','')
                            ->where(config('db_table_names.restaurant_review').'.body','!=','')
                            ->get();

            if (count($restaurant_info)) {
              //Check if the any data was matched.
                
                /*foreach ($restaurant_info as $info_item) {
                    //Append storage end point to each image
                    $info_item['photo_address'] = config('globals.storage_endpoint').$info_item['photo_address'];
                }
                */

                return response()->json($restaurant_info);   
            } 
            else {
                //Log error in case of empty query (no match found with current restaurant_id)
                Log::error('Failed  '.__METHOD__.'. No record found in restaurant_review table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
             
       
        }
        else {
            //Log error in case restaurant_id is blank or non-numeric
            Log::error('Failed '.__METHOD__.'. restaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }





    /*
    / Send the vat_rate, service_tax_rate, service_charge_rate for the given Restaurant (restaurant_id)
    / @param int $restaurant_id
    / @return JSON of vat_rate, service_tax_rate, service_charge_rate
    */

    public function getRestaurantTaxInfo($restaurant_id) {
        
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $restaurant_info = DB::table(config('db_table_names.restaurant'))
                            ->select(DB::raw('ROUND(vat_rate,2) as vat_rate, ROUND(service_tax_rate,2) as service_tax_rate, ROUND(service_charge_rate,2) as service_charge_rate'))
                            ->where('id',$restaurant_id)
                            ->get();

            if (count($restaurant_info)) {
              //Check if the any data was matched.
                return response()->json($restaurant_info);   
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



    public function fix_filter_list($restaurant_id) {
        
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
                DB::table(config('db_table_names.restaurant_view'))
                    ->where('restaurant_id', $restaurant_id)
                    ->update(['filter_list' => implode(",",$result_unique)]);
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



    /*
    / Get the menu's associated with a particular restaurant. 
    / @param int $restaurant_id
    / @param $package_type:'all' or 'package' or 'a-la-carte' depending on type of menu needed.
    / @return JSON array of menus with matching $restaurant_id
    */

    public function getRestaurantMenu($restaurant_id, $package_type='all') {
       
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            $query =  DB::table(config('db_table_names.menu'))     
                        ->where('restaurant_id',$restaurant_id);      
            /*
            / Exact compares the "package_type". 
            / Need to create an enum for the same in the DB to ensure match - DONE
            */

            switch ($package_type) {
                case 'all': 
                    break;

                case 'package': 
                    $query->where('type','package');
                    break;

                case 'a-la-carte': 
                    $query->where('type','a-la-carte');
                    break;
                
                default:
                    //Log error in case package value is anything else.
                    Log::error('Failed '.__METHOD__.'. Package should be from:all, package, a-la-carte',['restaurant_id'=>$restaurant_id]);
                    return response()->json(['Error' => config('globals.error_msg')]);   
                    break;

            }

            $menu = $query->get();

            if (count($menu)) {
                  //Check if any data was fetched.
                  return response()->json($menu);   
            } 
            else {
                //Log error in case an empty set was picked up (No menu for restaurant_id)
                Log::error('Failed '.__METHOD__.'. No record found in menu table.',['restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
            }
               
        }
        else {
            //Log error in case restaurant_id is blank or non-numeric
            Log::error('Failed '.__METHOD__.'. resstaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }
       
    }

   
    /*
    / Get the menu items associated with a particular menu. 
    / @param int $menu_id
    / @return JSON array of menus items with matching $menu_id
    */

    public function getRestaurantMenuItem ($restaurant_id, $menu_id) {
        
        //Validate if restaurant_id is set correctly
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {

            //Validate if menu_id is set correctly
            if (isset($menu_id) && is_numeric($menu_id)) {

                //Validate if menu belongs to this restaurant
                $menu_check = DB::table(config('db_table_names.menu'))
                                ->select('restaurant_id')
                                ->where('id','=',$menu_id)
                                ->get();
                if (count($menu_check)) {
                    
                    foreach($menu_check as $menu){

                        if ($menu->restaurant_id != $restaurant_id) {

                            //Menu does not belong to this restaurant_id
                            Log::error('Failed '.__METHOD__.'. menu_id does not belong to this restaurant_id.',['menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                            return response()->json(['Error' => config('globals.error_msg')]); 
                        }
                    } 
                }
                else {
                    //Menu_id does not exist.
                    Log::error('Failed '.__METHOD__.'. menu_id does not exist.',['menu_id'=>$restaurant_id, 'restaurant_id'=>$menu_id]);
                    return response()->json(['Error' => config('globals.error_msg')]);  

                }

                $menu_item = DB::table(config('db_table_names.menu_item'))
                                ->where('menu_id',$menu_id)
                                ->get();

                if (count($menu_item)) {
                  //Check if the any data was matched.
                  return response()->json($menu_item);   
                } 
                else {
                    //Log error in case of empty query.
                    Log::error('Failed '.__METHOD__.'. No record found in menu_item table.',['menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                    return response()->json(['Error' => config('globals.error_msg')]);        
              }
                   
            }
            else {
                
                //Log error in case menu_id is blank or non-numeric.
                Log::error('Failed '.__METHOD__.'. menu_id is not set or not numeric.',['menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);            
            }

        }
        else {

            //Log error in case restaurant_id is blank or non-numeric.
            Log::error('Failed '.__METHOD__.'. restaurant_id is not set or not numeric.',['menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);
        }
    }

   
    /*
    / Get the menu items option categoes list associated with a particular menu item. 
    / @param int $restaurant_id, int $menu_id, int $menu_item_id
    / @return JSON array of menus items option categories
    */

    public function getMenuItemOptionCategory ($restaurant_id, $menu_id, $menu_item_id) {
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {
            //Validate if restaurant_id is set and numeric
            
            if (isset($menu_id) && is_numeric($menu_id)) {
                //Validate if menu_id is set and numeric

                if(isset($menu_item_id) && is_numeric($menu_item_id)) {


                    //Validate if menu belongs to this restaurant
                    $menu_check = DB::table(config('db_table_names.menu'))
                                    ->select('restaurant_id')
                                    ->where('id','=',$menu_id)
                                    ->get();
                    if (count($menu_check)) {
                        
                        foreach($menu_check as $menu){

                            if ($menu->restaurant_id != $restaurant_id) {

                                //Menu does not belong to this restaurant_id
                                Log::error('Failed '.__METHOD__.'. menu_id does not belong to this restaurant_id.',['menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                                return response()->json(['Error' => config('globals.error_msg')]); 
                            }
                        } 
                    }

                    //Validate if menu_item belongs to this menu
                    $menu_item_check = DB::table(config('db_table_names.menu_item'))
                                    ->select('menu_id')
                                    ->where('id','=',$menu_item_id)
                                    ->get();
                    if (count($menu_item_check)) {
                        
                        foreach($menu_item_check as $menu_item){

                            if ($menu_item->menu_id != $menu_id) {

                                //Menu does not belong to this restaurant_id
                                Log::error('Failed '.__METHOD__.'. menu_id does not belong to this restaurant_id.',['menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                                return response()->json(['Error' => config('globals.error_msg')]); 
                            }
                        } 
                    }

                    $menu_item_option_category = DB::table(config('db_table_names.menu_item_option_category'))
                                    ->where('menu_item_id','=',$menu_item_id)
                                    ->get();

                    if (count($menu_item_option_category)) {
                      //Check if the any data was matched.
                      return response()->json($menu_item_option_category);   
                    } 
                    else {
                        //Log error in case of empty query.
                        Log::error('Failed '.__METHOD__.'. No record found in menu_item_option_category table.',['menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                        return response()->json(['Error' => config('globals.error_msg')]);        
                    }


                }
                else {

                //Log error in case menu_id is blank or non-numeric.
                Log::error('Failed '.__METHOD__.'. menu_item_id is not set or not numeric.',['menu_item_id'=>$menu_item_id,'menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);   
                }



            }
            else {

                //Log error in case menu_id is blank or non-numeric.
                Log::error('Failed '.__METHOD__.'. menu_id is not set or not numeric.',['menu_item_id'=>$menu_item_id,'menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);   
            }
        }
        else {

            //Log error in case restaurant_id is blank or non-numeric.
            Log::error('Failed '.__METHOD__.'. restaurant_id is not set or not numeric.',['menu_item_id'=>$menu_item_id,'menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);

        }
    }

  
    /*
    / Get the menu items options list associated with a particular menu item category. 
    / @param int $restaurant_id, int $menu_id, int $menu_item_id, int menu_item_option_category
    / @return JSON array of menus items option list
    */

    public function getMenuItemOptionList ($restaurant_id, $menu_id, $menu_item_id, $menu_item_option_category) {
        if (isset($restaurant_id) && is_numeric($restaurant_id)) {
            //Validate if restaurant_id is set and numeric
            
            if (isset($menu_id) && is_numeric($menu_id)) {
                //Validate if menu_id is set and numeric

                if(isset($menu_item_id) && is_numeric($menu_item_id)) {

                    if (isset($menu_item_option_category) && is_numeric($menu_item_option_category)) {



                        //Validate if menu belongs to this restaurant
                        $menu_check = DB::table(config('db_table_names.menu'))
                                        ->select('restaurant_id')
                                        ->where('id','=',$menu_id)
                                        ->get();
                        if (count($menu_check)) {
                            
                            foreach($menu_check as $menu){

                                if ($menu->restaurant_id != $restaurant_id) {

                                    //Menu does not belong to this restaurant_id
                                    Log::error('Failed '.__METHOD__.'. menu_id does not belong to this restaurant_id.',['menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                                    return response()->json(['Error' => config('globals.error_msg')]); 
                                }
                            } 
                        }

                        //Validate if menu_item belongs to this menu
                        $menu_item_check = DB::table(config('db_table_names.menu_item'))
                                        ->select('menu_id')
                                        ->where('id','=',$menu_item_id)
                                        ->get();
                        if (count($menu_item_check)) {
                            
                            foreach($menu_item_check as $menu_item){

                                if ($menu_item->menu_id != $menu_id) {

                                    //Menu does not belong to this restaurant_id
                                    Log::error('Failed '.__METHOD__.'. menu_item does not belong to this menu_id.',['menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                                    return response()->json(['Error' => config('globals.error_msg')]); 
                                }
                            } 
                        }

                        //Validate if menu_item_option_category belongs to this menu_item
                        $menu_item_option_check = DB::table(config('db_table_names.menu_item_option_category'))
                                        ->select('menu_item_id')
                                        ->where('id','=',$menu_item_option_category)
                                        ->get();
                        if (count($menu_item_option_check)) {
                            
                            foreach($menu_item_option_check as $menu_item_option){

                                if ($menu_item_option->menu_item_id != $menu_item_id) {

                                    //Menu does not belong to this restaurant_id
                                    Log::error('Failed '.__METHOD__.'. menu_item_option_category does not belong to this menu_item_id.',['menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                                    return response()->json(['Error' => config('globals.error_msg')]); 
                                }
                            } 
                        }

                        $menu_item_option_list = DB::table(config('db_table_names.menu_item_option_list'))
                                        ->where('menu_item_option_id','=',$menu_item_option_category)
                                        ->get();

                        if (count($menu_item_option_list)) {
                          //Check if the any data was matched.
                          return response()->json($menu_item_option_list);   
                        } 
                        else {
                            //Log error in case of empty query.
                            Log::error('Failed '.__METHOD__.'. No record found in menu_item_option_list table.',['menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                            return response()->json(['Error' => config('globals.error_msg')]);        
                        }
                    }
                    else {

                            //Log error in case menu_item_option_category_id is blank or non-numeric.
                            Log::error('Failed '.__METHOD__.'. menu_item_option_category is not set or not numeric.',['menu_item_id'=>$menu_item_id,'menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                            return response()->json(['Error' => config('globals.error_msg')]); 
                    }


                }
                else {

                //Log error in case menu_item_id is blank or non-numeric.
                Log::error('Failed '.__METHOD__.'. menu_item_id is not set or not numeric.',['menu_item_id'=>$menu_item_id,'menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);   
                }



            }
            else {

                //Log error in case menu_id is blank or non-numeric.
                Log::error('Failed '.__METHOD__.'. menu_id is not set or not numeric.',['menu_item_id'=>$menu_item_id,'menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
                return response()->json(['Error' => config('globals.error_msg')]);   
            }
        }
        else {

            //Log error in case restaurant_id is blank or non-numeric.
            Log::error('Failed '.__METHOD__.'. restaurant_id is not set or not numeric.',['menu_item_id'=>$menu_item_id,'menu_id'=>$menu_id, 'restaurant_id'=>$restaurant_id]);
            return response()->json(['Error' => config('globals.error_msg')]);

        }
    }

}
