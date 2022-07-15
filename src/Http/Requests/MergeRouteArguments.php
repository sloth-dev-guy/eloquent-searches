<?php

namespace SlothDevGuy\Searches\Http\Requests;

use Illuminate\Http\Request as BaseRequest;

/**
 * Trait MergeRouteArguments
 * @package SlothDevGuy\Searches\Http\Requests
 */
trait MergeRouteArguments
{
    /**
     * @return array
     */
    public static function routeArgumentKeys() : array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function routeArguments() : array
    {
        return collect(static::routeArgumentKeys())
            ->mapWithKeys(fn($key) => [$key => request()->route($key)])
            ->toArray();
    }

    /**
     * @param BaseRequest $from
     * @param mixed $to
     * @return BaseRequest
     */
    public static function createFrom(BaseRequest $from, $to = null) : BaseRequest
    {
        $request = BaseRequest::createFrom($from, $to);

        $request->merge(static::routeArguments());

        return $request;
    }
}
