<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotFoundWhenProduction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $env = config('app.env');
        if($env != "local"){
            return $this->unauthorized();
        }
        return $next($request);
    }

    private function unauthorized($message = null){
        return response()->json([
            'code' => Response::HTTP_FORBIDDEN,
            'message' => $message ? $message : 'You are unauthorized to access this resource',
        ], Response::HTTP_FORBIDDEN);
    }
}
