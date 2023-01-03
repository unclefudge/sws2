<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
// use Fideloper\Proxy\TrustProxies as Middleware;   comment out as part of laravel 8->9 upgrade
use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var string
     */
    //protected $headers = Request::HEADER_X_FORWARDED_ALL;  comment out as part of laravel 8->9 upgrade
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    //
    //  Below commented out for laravel 5.5 tp 5.6 upgrade  5/1/21
    //

    /**
     * The current proxy header mappings.
     *
     * @var array
     */
    /*
    protected $headers = [
        Request::HEADER_FORWARDED => 'FORWARDED',
        Request::HEADER_X_FORWARDED_FOR => 'X_FORWARDED_FOR',
        Request::HEADER_X_FORWARDED_HOST => 'X_FORWARDED_HOST',
        Request::HEADER_X_FORWARDED_PORT => 'X_FORWARDED_PORT',
        Request::HEADER_X_FORWARDED_PROTO => 'X_FORWARDED_PROTO',
    ];*/
}
