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
		$temp = json_decode($content, true);
		$pkges = $temp['1'];
		print_r($temp);
	}

}