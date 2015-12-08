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

    public function getRestaurantList(Request $request) {
    	if ($request->isMethod('post')) {
    		if ($request->has('locality_id') && $request->has('date') && $request->has('time') && $request->has('pax')) {

    			$date=date_create_from_format('Y#m#d H#i', $request->input('date').' '.$request->input('time'));
          $time=date_create_from_format('H#m',$request->input('time'));
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
