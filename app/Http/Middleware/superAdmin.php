<?php

namespace App\Http\Middleware;

use Closure;

class superAdmin
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
        if(!checkSuperAdmin()){
			return redirect('/dashboard');
		 }else{
			return $next($request);
		 }
    }
}
