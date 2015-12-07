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



/*
/ defining the group for Restaurant APIs
*/
$app->group([
	'prefix'=> config('globals.api_path').'/{restaurant_id}', 
	'namespace'=>'App\Http\Controllers\Restaurant',
	'middleware'=>'auth'],
	function($app) {
		
		$app->get('getRestaurant', function($restaurant_id) {
			return $restaurant_id;
		});

	});

