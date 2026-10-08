<?php

namespace App\Http;

use App\Http\Middleware\Admin;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CacheControl;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use Fruitcake\Cors\HandleCors;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\MissingPageRedirector\RedirectsMissingPages;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;

class Kernel extends HttpKernel
{
    protected $middleware = [
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        TrustProxies::class,
        HandleCors::class,
        RedirectsMissingPages::class,

    ];

    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            CacheControl::class,
        ],

        'api' => [
            'throttle:60,1',
            SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        ThrottleRequests::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,
        'cacheResponse' => CacheResponse::class,
        'doNotCacheResponse' => DoNotCacheResponse::class,
        'signed' => ValidateSignature::class,
        'admin' => Admin::class,
        'verified' => EnsureEmailIsVerified::class,

    ];
}
