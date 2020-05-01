<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
       'api/ticket/store',
       'api/ticket/show',
       'api/ticket/show-active-ticket',
       'api/ticket/show-completed-ticket',
               
    ];
}
