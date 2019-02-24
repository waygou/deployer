<?php

namespace Waygou\Deployer\Middleware;

use Closure;

class SameToken
{
    public function handle($request, Closure $next)
    {
        if ($request->input('deployer-token') != app('config')->get('deployer.token')) {
            return response()->json(['error' => 'Local and remote tokens are different. Please check both local and remote configuration tokens']);
        }

        return $next($request);
    }
}
