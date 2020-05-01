<?php

namespace App\Http\Middleware;

use Closure;

class securedevice
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
		if(!getSecureDevice()){  
			return redirect()->route('securedevice.show');
		 }else{  
			if(checkPassword180DaysOld() == false){
                return redirect()->route('user.newpasswordreset'); 
            }else{
                return $next($request);
            }
		 }
    }
}
