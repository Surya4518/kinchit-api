<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
   public function handle($request, Closure $next)
    {
        // dd($request);
        $response = $next($request);

        // $response->headers->set('Access-Control-Allow-Origin', '*');
        // $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        // $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
        $cookie = cookie('humans_21909=1');
        $response->withCookie($cookie);
        // dd($_COOKIE);
        return $response;
    }
}