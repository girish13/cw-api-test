<?php

namespace App\Http\Controllers;

//use Laravel\Lumen\Routing\Controller as BaseController;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Log;
use App\Http\Controllers\Controller;

class order extends Controller {

//

	/*
	/ ORDER JSON STRUCTURE
	/ ARRAY
	/	([0]=>cust_id,
	/	[1]=>PKG ARRAY
	/			([0]=>restaurant_id,
	/			[1]=>package_id,
	/			[2]=>PKG MENU ARRAY
	/					([item_id 1]=>ITEM ARRAY
	/									([menu_item_option_cat_id 1]=>OPTION CAT ARRAY
	/																([0]=>OPTION CAT LIST ARRAY
	/																		([id]=>menu_item_option_list_id,
	/																		[menu_item_option_id]=>menu_item_option_cat_id,
	/																		[name]=>name of menu_item_option_list,
	/																		[price]=>Additional price of menu_item_option_list
	/																		)
	/																[1]=>OPTION CAT LIST ARRAY - Repeat above structure
	/																[]=>OPTION CAT LIST ARRAY - Repeat above structure
	/																)
	/									[menu_item_option_cat_id 2]=>OPTION CAT ARRAY - Repeat above structure
	/									)
	/					[item_id 2]=>ITEM ARRAY - Repeat above structure
	/					)
	/			[3]=>PAX
	/			)
	/	[2]=>PKG ARRAY - Repeat above structure
	/	)
	*/


	public function validateOrder(Request $request) {
		$content = $request->getContent();
		$order = json_decode($content, true);
		echo count($order);
		$total_pax=0;
		// Extract package out of Array
		for ($x=1; $x<count($order);$x++) {
			$package = $order[$x];
			$restaurant_id = $package['0'];
			$package_id = $package['1'];
			//TO DO - Check if this restaurant has this package else Error.
			$menu = $package['2'];
			$pax = $package['3'];
			$total_pax+=$pax;
			$menu_item_ids = array_keys($menu);
			//print_r($menu_item_ids);
			
			foreach($menu_item_ids as $menu_item_id) {
				//To DO  - Check if menu_item belongs to menu
				//INSERT item_id and package_id
				$menu_item_options_cat = $menu[$menu_item_id];
				$menu_item_options_cat_ids=array_keys($menu_item_options_cat);
				print_r($menu_item_options_cat_ids);
				
				foreach($menu_item_options_cat_ids as $menu_item_options_cat_id) {

					//INSERT menu_item_options_cat_id and menu_item_id
					$menu_item_options_list=$menu_item_options_cat[$menu_item_options_cat_id];
					//print_r($menu_item_options_cat_id);
					for ($y=0; $y<count($menu_item_options_list); $y++) {
						//print_r($menu_item_options_list[$y]);
						//Insert $menu_item_options_list[$y]['id'] and $menu_item_options_cat_id
					}
				}
				//print_r($menu_item_option);
			}

			//print_r($package);

		}

		//$pkges = $temp['1'];
		//print_r(array_keys($pkges[2]));
	}

}