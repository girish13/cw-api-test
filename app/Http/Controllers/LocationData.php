<?php

namespace App\Http\Controllers;

//use Laravel\Lumen\Routing\Controller as BaseController;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Log;
use App\Http\Controllers\Controller;

class LocationData extends Controller
{
    //

	 /*
    / Send the list of states
    / @param NONE
    / @return JSON of all states
    */

    public function getStates() {
        
            $state_list = DB::table(config('db_table_names.state'))
                            ->get();

            if (count($state_list)) {
              //Check if the any data was matched.
              return response()->json($state_list);   
            } 
            else {
                //Log error in case of empty query (no match found with current restaurant_id)
                Log::error('Failed '.__METHOD__.'. No record found in state table.');
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
               
    }

    /*
    / Send the list of cities for a particular state
    / @param int $state_id
    / @return JSON of all cities within state
    */

    public function getCities($state_id) {
        
        if (isset($state_id) && is_numeric($state_id)) {

            $city_info = DB::table(config('db_table_names.city'))
                            ->where('state_id',$state_id)
                            ->get();

            if (count($city_info)) {
              //Check if the any data was matched.
              return response()->json($city_info);   
            } 
            else {
                //Log error in case of empty query (no match found with current restaurant_id)
                Log::error('Failed '.__METHOD__.'. No record found in restaurant_schedule table.',['state_id'=>$state_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
               
        }
        else {
            //Log error in case restaurant_id is blank or non-numeric
            Log::error('Failed '.__METHOD__.'. restaurant_id is not set or not numeric.',['state_id'=>$state_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }


    /*
    / Send the list of localities for a particular state
    / @param int $state_id, $city_id
    / @return JSON of all localities within state
    */

    public function getLocalities($state_id,$city_id) {
        
       // TO DO - CHECK IF CITY IN STATE

        if (isset($city_id) && is_numeric($city_id)) {

            $locality_info = DB::table(config('db_table_names.locality'))
                            ->where('city_id',$city_id)
                            ->get();

            if (count($locality_info)) {
              //Check if the any data was matched.
              return response()->json($locality_info);   
            } 
            else {
                //Log error in case of empty query (no match found with current restaurant_id)
                Log::error('Failed '.__METHOD__.'. No record found in restaurant_schedule table.',['city_id'=>$city_id]);
                return response()->json(['Error' => config('globals.error_msg')]);        
          }
               
        }
        else {
            //Log error in case restaurant_id is blank or non-numeric
            Log::error('Failed '.__METHOD__.'. restaurant_id is not set or not numeric.',['city_id'=>$city_id]);
            return response()->json(['Error' => config('globals.error_msg')]);            
        }

    }





}
