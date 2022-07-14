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
        $this->app->resolving(SearchRequest::class, fn($request, $app) => SearchRequest::createFrom($app['request'], $request));

        $this->app->afterResolving(SearchRequest::class, fn(SearchRequest $request) => $request->validate());
    }
}
