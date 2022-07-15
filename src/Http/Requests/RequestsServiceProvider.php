<?php

namespace SlothDevGuy\Searches\Http\Requests;

use Illuminate\Support\ServiceProvider;

/**
 * Class RequestsServiceProvider
 * @package GICU\DataGathering\Requests
 */
class RequestsServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->app->resolving(
            Request::class,
            fn($request, $app) => $request::createFrom($app['request'], $request)
        );

        $this->app->afterResolving(
            Request::class,
            fn(Request $request) => $request->validate()
        );
    }
}
