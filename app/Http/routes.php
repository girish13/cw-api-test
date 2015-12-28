<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->welcome();
});

$app->get('/test', function () use ($app) {
    return "Hello World";
});

/*
/ defining the group for Restaurant APIs
*/
$app->group([
	'prefix'=> config('globals.api_path').'/getRestaurant/{restaurant_id}', 
	'namespace'=>'App\Http\Controllers',
	'middleware'=>'auth'],
	function($app) {
		$app->get('/', ['uses'=> 'RestaurantDisplay@getRestaurantInfo']);
		$app->get('/schedule', ['uses'=> 'RestaurantDisplay@getRestaurantSchedule']);
		$app->get('/images', ['uses'=> 'RestaurantDisplay@getRestaurantImages']);
		$app->get('/menu', ['uses'=> 'RestaurantDisplay@getRestaurantMenu']);
		$app->get('/tax', ['uses'=> 'RestaurantDisplay@getRestaurantTaxInfo']);
		$app->get('/fix_filter_list', ['uses'=> 'RestaurantDisplay@fix_filter_list']);
		$app->get('/review', ['uses'=> 'RestaurantDisplay@getRestaurantReview']);
		$app->get('/menu/{package_type}', ['uses'=> 'RestaurantDisplay@getRestaurantMenu']);
		$app->get('/menu/{menu_id}/menuItem', ['uses'=> 'RestaurantDisplay@getRestaurantMenuItem']);
		$app->get('/menu/{menu_id}/menuItem/{menu_item_id}/menuItemOptionCategory', ['uses'=> 'RestaurantDisplay@getMenuItemOptionCategory']);
		$app->get('/menu/{menu_id}/menuItem/{menu_item_id}/menuItemOptionCategory/{menu_item_option_category}/menuItemOptionList', ['uses'=> 'RestaurantDisplay@getMenuItemOptionList']);
		//$app->get('/getMenuItemOptionAndList/{menu_id}/{menu_item_id}', ['uses'=> 'RestaurantDisplay@getMenuItemOptionAndList']);$app->get('/menu/{menu_id}/menuItem/{menu_item_id}/menuItemOptionCategory', ['uses'=> 'RestaurantDisplay@getMenuItemOptionCategory']);
	});

$app->group([
	'prefix'=> config('globals.api_path').'/getLocation', 
	'namespace'=>'App\Http\Controllers',
	'middleware'=>'auth'],
	function($app) {
		$app->get('/states', ['uses'=> 'LocationData@getStates']);
		$app->get('/state/{state_id}/cities', ['uses'=> 'LocationData@getCities']);
		$app->get('/state/{state_id}/city/{city_id}/localities', ['uses'=> 'LocationData@getLocalities']);
		//$app->get('/getMenuItemOptionAndList/{menu_id}/{menu_item_id}', ['uses'=> 'RestaurantDisplay@getMenuItemOptionAndList']);$app->get('/menu/{menu_id}/menuItem/{menu_item_id}/menuItemOptionCategory', ['uses'=> 'RestaurantDisplay@getMenuItemOptionCategory']);
	});


$app->group([
	'prefix'=> config('globals.api_path').'/', 
	'namespace'=>'App\Http\Controllers',
	'middleware'=>'auth'],
	function($app) {
		$app->post('getRestaurantList', ['uses'=> 'RestaurantList@getRestaurantList']);
		$app->get('getRestaurantList', ['uses'=> 'RestaurantList@getRestaurantList']);
	});


$app->group([
	'prefix'=> config('globals.api_path').'/getFilters', 
	'namespace'=>'App\Http\Controllers',
	'middleware'=>'auth'],
	function($app) {
		$app->get('/', ['uses'=> 'getFilters@getAllFilters']);
		$app->get('/getTypes', ['uses'=> 'getFilters@getAllFilterTypes']);
		$app->get('/getFilterByType/{filter_type}', ['uses'=> 'getFilters@getAllFiltersByType']);
	});

$app->group([
	'prefix'=> config('globals.api_path').'/order', 
	'namespace'=>'App\Http\Controllers',
	'middleware'=>'auth'],
	function($app) {
		$app->post('/', ['uses'=> 'order@validateOrder']);

	});


$app->group([
	'prefix'=> config('globals.api_path').'/util', 
	'namespace'=>'App\Http\Controllers',
	'middleware'=>'auth'],
	function($app) {
		$app->get('/getOrderTimeList', ['uses'=> 'cwUtil@getOrderTimeList']);
	});


