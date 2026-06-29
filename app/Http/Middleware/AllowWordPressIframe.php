<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowWordPressIframe
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        /*
         * Remove old iframe blocking header if present.
         * X-Frame-Options SAMEORIGIN will block embedding on WordPress.
         */
        $response->headers->remove('X-Frame-Options');

        /*
         * Allow this form to be embedded only by Cape Cod / WordPress.
         */
        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors 'self' https://capecod.com.au https://www.capecod.com.au;"
        );

        return $response;
    }
}