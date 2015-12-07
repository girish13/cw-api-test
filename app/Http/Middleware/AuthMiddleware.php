<?php

namespace App\Http\Middleware;

use Closure;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //echo "hello from middleware beging";
        //blank function right now. needs to filled with auth functions later on.
        $response =  $next($request);

        //HACK FOR CROSS SITE TESTING. NEEDS TO BE REMOVED BEFORE GOING LIVE FOR PRODUCTION
        return $response->header('Access-Control-Allow-Origin','*');

       //echo "hello from middleware end";
    }
}
