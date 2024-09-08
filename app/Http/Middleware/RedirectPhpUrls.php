<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectPhpUrls
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the URL ends with .php
        if ($request->is('*/*.php')) {
            // Redirect to the homepage
            return redirect('/');
        }

        return $next($request);
    }
}
