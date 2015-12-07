<?php

namespace App\Http\Controllers;

use App\User;
//use Laravel\Lumen\Routing\Controller as BaseController;

class getRestaurant extends Controller
{
    /*
    / Restaurant Display page controller
    */

    /*
    / Send the basic information of the Restaurant
    / @param int $restaurant_id
    / @return JSON
    */

    public function getShortInfo($restaurant_id) {
    	return "Hello from Controller";
    }
}
