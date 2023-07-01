<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiHeadersMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->header('X-Api-Version', $request->header('X-Api-Version', 'v1'));
        $response->header('X-Api-Latest-Version', config('app.latestApiVersion'));
        $response->header('X-Api-Has-Newer-Version', $request->header('X-Api-Version', 'v1') !== config('app.latestApiVersion'));

        return $response;
    }
}
