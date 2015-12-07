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

    			//get all locality_ids eligible for delivery in this area
    			$locality_id= $request->input('locality_id');
    			$locality_id_set = new array();
    			$locality_id_data_set = DB::table(config('db_table_names.locality_serve'))
                            ->where('locality_id_1',$locality_id)
                            ->orWhere('locality_id_2',$locality_id)
                            ->get();

                foreach ($locality_id_data_set as $id) {
                	if ($id->locality_id_1 != $locality_id) array_push($locality_id_set, $id->locality_id_1);
                	else if ($id->locality_id_2 != $locality_id) array_push($locality_id_set, $id->locality_id_2);
              	}
              	array_push($locality_id_set, $locality_id);
              	array_unique($locality_id_set); //Not needed, but extra precautions.
              	
foreach

              	($locality_id_set as $id) {

              	}

              	implode(' OR ', pieces)


][	
    		}
    	}

    }



}
