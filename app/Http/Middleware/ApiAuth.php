<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\DB;
use Closure;
use Illuminate\Http\Request;

use App\AccessTokens;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
	
/*	public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
	{
		$key = $request;
		
		if ($this->limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
			return new Response('Too Many Attempts.', 429, [
					'Retry-After'           => $this->limiter->availableIn($key),
					'X-RateLimit-Limit'     => $maxAttempts,
					'X-RateLimit-Remaining' => 0,
			]);
		}
		
		$this->limiter->hit($key, $decayMinutes);
		
		$response = $next($request);
		$response->headers->set('X-RateLimit-Limit', $maxAttempts);
		$response->headers->set('X-RateLimit-Remaining', $maxAttempts - $this->limiter->attempts($key) + 1);
		
		return $response;
	} */
	
	
	
    public function handle($request, Closure $next)
    {
    	$status = false;
    	
    	 
    	try {
    		$accessToken = $request->header('Authorization');
    		
    		if($accessToken)
    		{
    			$user_check = DB::select(DB::raw("select * from _fis_access_tokens where api_token='".$accessToken."' and date_expire>='".date('Y-m-d H:i:s')."'"));
    			
    			try {
    				if(count($user_check)>0)
    				{
    					$status = true;
    					
    				}
    			} catch (Exception $e) {
    				return response("no can do", 404, ['Content-Type'=>'application/json']);
    			}
    			
    			if($status == false) {
    				$response = [
    						'status' => 'invalid',
    						'reason' => 'Access Forbidden'
    				];
    				return response(json_encode($response), 403, ['Content-Type'=>'application/json']);
    			} else {
    				return $next($request);
    			}
    			
    		}
    	} catch (\Exception $e) {
    		return response($e->getMessage(), 404, ['Content-Type'=>'application/json']);
    	}
    	
      //  return $next($request);
    } 
}
