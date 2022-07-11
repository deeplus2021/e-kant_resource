<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2021/3/18
 * Time: 16:01
 */

namespace App\Http\Middleware;

use Closure;

class HttpsProtocol
{
    public function handle($request, Closure $next)
    {
        if (!$request->secure() && env('APP_ENV') === 'production') {
            return redirect()->secure($request->getRequestUri());
        }
        return $next($request);
    }
}