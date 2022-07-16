<?php

namespace SlothDevGuy\Searches\Http\Requests;

/**
 * Class SearchRequest
 * @package GICU\DataGathering\Requests
 */
class SearchRequest extends Request
{
    /**
     * @inheritDoc
     * @param $keys
     * @return array
     */
    public function all($keys = null)
    {
        return collect(parent::all($keys))->except(static::reservedKeys())
            ->toArray();
    }

    /**
     * @return string[]
     */
    public static function reservedKeys() : array
    {
        return ['page', 'max', 'distinct', 'order'];
    }
}
