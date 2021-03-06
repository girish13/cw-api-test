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
				if ($id->locality_id_1 != $locality_id) array_push($locality_id_set, (int)$id->locality_id_1);
				else if ($id->locality_id_2 != $locality_id) array_push($locality_id_set, (int)$id->locality_id_2);
			}

			array_push($locality_id_set, (int)$locality_id); //type conversion to int to ensure uniformity in array elements. POST parameters are string by default
			$result=array_unique($locality_id_set); //Not needed, but extra precautions.
			asort($result); //Not needed, for readability.
			return (array)$result;
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
														->select(config('db_table_names.restaurant_view').'.id as id',config('db_table_names.restaurant_view').'.name','short_description', 'cuisines','profile_photo',
																'avg_rating','review_count','max_package_price','min_package_price',
																'min_order_value','min_order_count','total_orders','order_before','cancel_before',config('db_table_names.locality').'.name as locality_name',config('db_table_names.city').'.name as city_name')
														->join(config('db_table_names.locality'), config('db_table_names.locality').'.id','=',config('db_table_names.restaurant_view').'.locality_id')
														->join(config('db_table_names.city'), config('db_table_names.city').'.id','=',config('db_table_names.restaurant_view').'.city_id')
														->where('id',$restaurant_id)
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
														->select(config('db_table_names.restaurant_view').'.id as id',config('db_table_names.restaurant_view').'.name','short_description', 'cuisines','profile_photo',
																'avg_rating','review_count','max_package_price','min_package_price',
																'min_order_value','min_order_count','total_orders','order_before','cancel_before',config('db_table_names.locality').'.name as locality_name',config('db_table_names.city').'.name as city_name')
														->join(config('db_table_names.locality'), config('db_table_names.locality').'.id','=',config('db_table_names.restaurant_view').'.locality_id')
														->join(config('db_table_names.city'), config('db_table_names.city').'.id','=',config('db_table_names.restaurant_view').'.city_id')
														->whereIn(config('db_table_names.restaurant_view').'.id',$restaurant_id)
														->orderBy($sort_var,$sort_order)
														->skip((($page-1)*config('globals.list_page_size')))
														->take(config('globals.list_page_size'))
														->get();

						if (count($restaurant_info)) {

							foreach($restaurant_info as $restaurant) {
                    
                if ($restaurant->profile_photo == "") {
                    $restaurant->profile_photo=config('globals.storage_endpoint').config('globals.restaurant_img_path').config('globals.default_logo');
                }
                else {
                    $restaurant->profile_photo=config('globals.storage_endpoint').config('globals.restaurant_img_path').$restaurant->profile_photo;
                }
		          }
              
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


		public function getEligibleFilterTypes($request_filters, $rest_id=0) {
			
			//Scope does not permit access in nested DB Facade. Hence accessing it as GLOBALS
			if (!isset($GLOBALS['restaurant_id_for_filter_eligibilty'])) global $restaurant_id_for_filter_eligibilty;
			$GLOBALS['restaurant_id_for_filter_eligibilty'] = $rest_id;
			
			$filter_list = array();
			$filter_list = explode(',', $request_filters);

			$filter_type = array();


			if (count($filter_list)) {
				$query = DB::table(config('db_table_names.filter_list'))
																					 ->select(DB::raw('DISTINCT '.config('db_table_names.filter_list').'.type as type'))

																					 ->whereIn(config('db_table_names.filter_list').'.id',$filter_list)
																					 ->orderBy('type','asc');
				
				if ($rest_id) {
					$query->join(config('db_table_names.filter'), function ($join) {
																				$join->on(config('db_table_names.filter').'.filter_list_id','=',config('db_table_names.filter_list').'.id')	
																						 ->where(config('db_table_names.filter').'.restaurant_id','=', $GLOBALS['restaurant_id_for_filter_eligibilty']);	
																			  });
				}

				$filter_type_result = $query->get();

				if (count($filter_type_result)){
					foreach ($filter_type_result as $type) {
						array_push($filter_type, $type->type);
					}	
				}
			}
			else {
					Log::error('Failed '.__METHOD__.'. Invalid filter_list',['$request_filters'=>$request_filters, 'rest_id'=>$rest_id]);
			}

			return $filter_type;
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
		/ @param(inside request) - price_max: INT
		/ @param(inside request) - price_min: INT
		/ @return  JSON of with sorted array of 10 restaurant_view row ('restaurant_id','name','short_description','cuisines',
		/'profile_photo','avg_rating','review_count','max_package_price','min_package_price','min_order_value',
		/'min_order_count','total_order','order_before','cancel_before','locality_id','city_id','state_id') matching the supplied $restaurant_id and $page number
		/ OR @return 0 in case no records on the requested page
		*/


		public function getRestaurantList(Request $request) {

			if ($request->has('locality_id')) {

				//Scope does not permit access in nested DB Facade. Hence accessing it as GLOBALS
				if (!isset($GLOBALS['requestDay'])) global $requestDay; 
				
				//Get locality_ids which delivery in this area.
				$locality_id_set = $this->getDeliveryLocalities($request->input('locality_id'));

				if (count($locality_id_set)) {
					//if Locality_ids exist, fetch eligible restaurants for those localities.
					$restaurant_query=DB::table(config('db_table_names.restaurant_view'))
															 ->select(config('db_table_names.restaurant_view').'.id','filter_list')
															 ->whereIn('locality_id', $locality_id_set);


					if ($request->has('date'))
						{
							//If date supplied, get the day of the week on which the order takes place
							$date = date_create_from_format('Y#m#d', $request->input('date'));
							$requestDay = $date->format('l');

							//join table to check if restaurant open on the event date.
							$restaurant_query->join(config('db_table_names.restaurant_schedule'), function ($join) {
																	$join->on(config('db_table_names.restaurant_schedule').'.restaurant_id','=',config('db_table_names.restaurant_view').'.id')
																				->where(config('db_table_names.restaurant_schedule').'.days','=', $GLOBALS['requestDay'])
																				->where(config('db_table_names.restaurant_schedule').'.open','=','1');
															 });

						}											

					//If price_max paremeter set, apply price max to the list.
					if ($request->has('price_max')) $restaurant_query->where(config('db_table_names.restaurant_view').'.max_package_price','<=',$request->input('price_max'));

					//If price_min parameter set, apply price min to the list.
					if ($request->has('price_min')) $restaurant_query->where(config('db_table_names.restaurant_view').'.min_package_price','>=',$request->input('price_min'));

					//Execute query
					$restaurant_list=$restaurant_query->get();

					$restaurant_id_set = array();	//carry the eligible restaurant IDs which fullfill the search criteria.

					if (count($restaurant_list)) {
						
						if ($request->has('filters')) {
							$request_filter_types = $this->getEligibleFilterTypes($request->input('filters'));
							foreach ($restaurant_list as $list) {
								if (!count(array_diff($request_filter_types,$this->getEligibleFilterTypes($request->input('filters'),$list->id)))) {
									array_push($restaurant_id_set, $list->id);
								}
							}
						}
						else {
							foreach ($restaurant_list as $list) {
								array_push($restaurant_id_set, $list->id);
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
