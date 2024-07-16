<?php

namespace SlothDevGuy\Searches;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SlothDevGuy\Searches\Http\Requests\SearchRequest;

class EloquentSearchServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->app->resolving(
            SearchRequest::class,
            fn($request, $app) => $request::createFrom($app['request'], $request)
        );

        $this->app->afterResolving(
            SearchRequest::class,
            fn(SearchRequest $request) => $request->validate()
        );
    }
}
