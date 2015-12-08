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
	'prefix'=> config('globals.api_path').'/{restaurant_id}', 
	'namespace'=>'App\Http\Controllers',
	'middleware'=>'auth'],
	function($app) {
		$app->get('RestaurantDisplay', ['uses'=> 'RestaurantDisplay@getRestaurantListView']);
		$app->get('RestaurantDisplay/schedule', ['uses'=> 'RestaurantDisplay@getRestaurantSchedule']);
		$app->get('RestaurantDisplay/images', ['uses'=> 'RestaurantDisplay@getRestaurantImages']);
		$app->get('RestaurantDisplay/all', ['uses'=> 'RestaurantDisplay@getAllRestaurants']);
		$app->get('getRestaurantMenu', ['uses'=> 'RestaurantDisplay@getRestaurantMenu']);
		$app->get('getRestaurantMenu/{package_type}', ['uses'=> 'RestaurantDisplay@getRestaurantMenu']);
		$app->get('getMenuItem/{menu_id}', ['uses'=> 'RestaurantDisplay@getMenuItem']);
		$app->get('getMenuItemOptionAndList/{menu_id}/{menu_item_id}', ['uses'=> 'RestaurantDisplay@getMenuItemOptionAndList']);
	});

$app->group([
	'prefix'=> config('globals.api_path').'/', 
	'namespace'=>'App\Http\Controllers',
	'middleware'=>'auth'],
	function($app) {
		$app->post('getRestaurantList', ['uses'=> 'RestaurantList@getRestaurantList']);
	});
