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
			//echo count($locality_id_set);
			return (array)$locality_id_set;
		}


		/*
		/ Send the basic information of the Restaurant
		/ @param int $restaurant_id 
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


		/*
		/ Send array of 10 (equal to 1 page) restaurants with basic information of the Restaurant
		/ @param array int $restaurant_id 
		/ @param int $page - Identifies which page to call for
		/ @param string $sort - Can be 'popularity' (default), 'price-low','price-high','rating'
		/ @return  JSON of with sorted array of 10 restaurant_view row ('restaurant_id','name','short_description','cuisines',
		/'profile_photo','avg_rating','review_count','max_package_price','min_package_price','min_order_value',
		/'min_order_count','total_order','order_before','cancel_before','locality_id','city_id','state_id') matching the supplied $restaurant_id and $page number
		/ (DOES NOT RETURN ADDRESS AND LONG DESCRIPTION FIELDS)
		*/

		public function getRestaurantListViewByPage(array $restaurant_id, $page='1', $sort='popularity') {
			
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
					default:
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
														->whereIn('restaurant_id',$restaurant_id)
														->orderBy($sort_var,$sort_order)
														->skip((($page-1)*10))
														->take(config('globals.list_page_size'))
														->get();

						if (count($restaurant_info)) {
							//Check if the any data was matched.
							return $restaurant_info;   
						} 
						else {
								//Log error in case of empty query
								Log::error('Failed getRestaurantListView. No record found in restaurant_view table.',['restaurant_id'=>$restaurant_id]);
								return 0;    
						}
							 
				}
				else {
						Log::error('Failed getRestaurantListView. restaurant_id is not set or not numeric.',['restaurant_id'=>$restaurant_id]);
						return 0;          
				}

		}


		/*
		/ Get list of eligible restaurants along with details from which a customer can order in as per the order specification
		/ Request object with - Locality_id, date, time, pax, sort(optional), page(optional)
		/ @param(inside request) - locality_id: numeric ID
		/ @param(inside request) - date: YYYY/MM/DD
		/ @param(inside request) - time: hh/mm A (12 Hours)
		/ @param(inside request) - pax: INT (less than 50)
		/ @param(inside request) - sort: 'popularity', 'rating', 'price-low','price-high'
		/ @param(inside request) - page: INT (start from 1) 
		/ @paraem(inside request) - filters: comma separate string of FILTER IDs
		/ @param(inside request) - price-max: INT
		/ @param(inside request) - price-min: INT
		/ @return  JSON of with sorted array of 10 restaurant_view row ('restaurant_id','name','short_description','cuisines',
		/'profile_photo','avg_rating','review_count','max_package_price','min_package_price','min_order_value',
		/'min_order_count','total_order','order_before','cancel_before','locality_id','city_id','state_id') matching the supplied $restaurant_id and $page number
		/ OR @return 0 in case no records on the requested page
		*/


		public function getRestaurantList(Request $request) {

			if ($request->has('locality_id') && $request->has('date') && $request->has('time') && $request->has('pax')) {

				$date = date_create_from_format('Y#m#d h#i A', $request->input('date').' '.$request->input('time'));

				
				//Scope does not permit access in nested DB Facade. Hence accessing it as GLOBALS
				if (!isset($GLOBALS['requestDay'])) global $requestDay; 
				$requestDay = $date->format('l');
				
				//Get locality_ids which delivery in this area.
				$locality_id_set = $this->getDeliveryLocalities($request->input('locality_id'));
				
				if (count($locality_id_set)) {
					$restaurant_query=DB::table(config('db_table_names.restaurant_view'))
															 ->select(config('db_table_names.restaurant_view').'.restaurant_id','filter_list')
															 ->join(config('db_table_names.restaurant_schedule'), function ($join) {
																	$join->on(config('db_table_names.restaurant_schedule').'.restaurant_id','=',config('db_table_names.restaurant_view').'.restaurant_id')
																				->where(config('db_table_names.restaurant_schedule').'.days','=', $GLOBALS['requestDay'])
																				->where(config('db_table_names.restaurant_schedule').'.open','=','1');
															 })
															->whereIn('locality_id', $locality_id_set);


					if ($request->has('price-max')) $restaurant_query->where(config('db_table_names.restaurant_view').'.max_package_price','<=',$request->input('price-max'));
					if ($request->has('price-min')) $restaurant_query->where(config('db_table_names.restaurant_view').'.min_package_price','>=',$request->input('price-min'));
					$restaurant_list=$restaurant_query->get();
					
					$restaurant_id_set = array();	//carry the eligible restaurant IDs which fullfill the search criteria.

					if (count($restaurant_list)) {

						$filter_list = array();
						if ($request->has('filters')) $filter_list=explode(',',$request->input('filters'));
						sort($filter_list);
						
						if (count($filter_list)) {
						foreach ($restaurant_list as $list) {
							//array_push($restaurant_id_set, $list->restaurant_id);
							$filter_curr = array();
								if(isset($list->filter_list)) {
									$filter_curr=explode(",",$list->filter_list);
									sort($filter_curr);
									if (implode(",",array_intersect($filter_curr, $filter_list)) == implode(",",$filter_list)) {
										array_push($restaurant_id_set, $list->restaurant_id);
									}
								}
							}
						}
						else {
							foreach ($restaurant_list as $list) {
								array_push($restaurant_id_set, $list->restaurant_id);
							}
						}
											
					if ($request->has('page') && is_numeric($request->input('page')) && $request->has('sort')) {
						$restaurant_info = $this->getRestaurantListViewByPage($restaurant_id_set, $request->input('page'), $request->input('sort'));
					}
					else if ($request->has('page') && is_numeric($request->input('page'))) {
						$restaurant_info = $this->getRestaurantListViewByPage($restaurant_id_set, $request->input('page'));
					}  
					else if($request->has('sort')) {
						 $restaurant_info = $this->getRestaurantListViewByPage($restaurant_id_set,1,$request->input('sort'));
					}
					else {
						$restaurant_info = $this->getRestaurantListViewByPage($restaurant_id_set);
					}
						
					if (count($restaurant_info)) {
						//returns the JSON or return 0 in case no records are found on requested page.
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
