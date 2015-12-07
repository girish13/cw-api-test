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

$app->get('test', function() {
	
	//$environ = app()->environment();
	
	if ($_ENV['APP_ENV']) {
    // The environment is local
		return "Hello World";
	} else {
		return "Hello World Global";
	}

});
