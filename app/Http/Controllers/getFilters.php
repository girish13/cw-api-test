<?php

namespace App\Http\Controllers;

//use Laravel\Lumen\Routing\Controller as BaseController;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Log;
use App\Http\Controllers\Controller;

class getFilters extends Controller {

//

	/*
	/ Get a list of all filters which can be applied to a restaurant
	/ @return JSON of all filter with (id, name, type)
	*/

	public function getAllFilters() {
        
      $filter_list = DB::table(config('db_table_names.filter_list'))
                      ->get();

      if (count($filter_list)) {
        //Check if the any data was matched.
        return response()->json($filter_list);   
      } 
      else {
          //Log error in case of empty query (no match found)
          Log::error('Failed '.__METHOD__.'. No record found in filter_list table.');
          return response()->json(['Error' => config('globals.error_msg')]);        
  		}
  	}



	/*
	/ Get a list of all filter types which can be applied to a restaurant
	/ @return JSON of all distinct filter types with.
	*/

  public function getAllFilterTypes() {
      
      $filter_type_list = DB::table(config('db_table_names.filter_list'))
	   				->select(DB::raw('distinct type'))
	               ->get();

      if (count($filter_type_list)) {
        //Check if the any data was matched.
        return response()->json($filter_type_list);   
      } 
      else {
          //Log error in case of empty query (no match found)
          Log::error('Failed '.__METHOD__.'. No record found in filter_list table.');
          return response()->json(['Error' => config('globals.error_msg')]);        
		}
	}


	/*
	/ get a list of all available filters within a particular filter type
	/ @param - STRING filter_type
	/ @return 
	*/

	public function getAllFiltersByType($filter_type) {
      
		if (isset($filter_type) and !is_null($filter_type)) {
			$filter_type_list = DB::table(config('db_table_names.filter_list'))
							->where('type','=',urldecode($filter_type))
			            ->get();

			if (count($filter_type_list)) {
			  //Check if the any data was matched.
			  return response()->json($filter_type_list);   
			} 
			else {
			    //Log error in case of empty query (no match found with current filter_type)
			    Log::error('Failed '.__METHOD__.'. No record found in filter_list table.',['filter_type'=>$filter_type,]);
			    return response()->json(['Error' => config('globals.error_msg')]);     
			}
		}   

		else {
			//Log error in case of empty query (no match found with current filter_type)
		    Log::error('Failed '.__METHOD__.'. $filter_type is not set or null.');
		    return response()->json(['Error' => config('globals.error_msg')]);   
  		}
               
	}



}