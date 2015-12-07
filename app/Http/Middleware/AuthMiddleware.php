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
        return $next($request);
       //echo "hello from middleware end";
    }
}
