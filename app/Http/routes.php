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
		$app->get('/menu/{package_type}', ['uses'=> 'RestaurantDisplay@getRestaurantMenu']);
		$app->get('/menu/{menu_id}/menuItem', ['uses'=> 'RestaurantDisplay@getRestaurantMenuItem']);
		$app->get('/menu/{menu_id}/menuItem/{menu_item_id}/menuItemOptionCategory', ['uses'=> 'RestaurantDisplay@getMenuItemOptionCategory']);
		$app->get('/menu/{menu_id}/menuItem/{menu_item_id}/menuItemOptionCategory/{menu_item_option_category}/menuItemOptionList', ['uses'=> 'RestaurantDisplay@getMenuItemOptionList']);
		//$app->get('/getMenuItemOptionAndList/{menu_id}/{menu_item_id}', ['uses'=> 'RestaurantDisplay@getMenuItemOptionAndList']);$app->get('/menu/{menu_id}/menuItem/{menu_item_id}/menuItemOptionCategory', ['uses'=> 'RestaurantDisplay@getMenuItemOptionCategory']);
	});

$app->group([
	'prefix'=> config('globals.api_path').'/', 
	'namespace'=>'App\Http\Controllers',
	'middleware'=>'auth'],
	function($app) {
		$app->post('getRestaurantList', ['uses'=> 'RestaurantList@getRestaurantList']);
	});
