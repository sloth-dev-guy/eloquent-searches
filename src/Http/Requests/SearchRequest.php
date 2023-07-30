<?php

namespace SlothDevGuy\Searches\Http\Requests;

use Illuminate\Http\Request;

/**
 * Class SearchRequest
 * @package GICU\DataGathering\Requests
 */
class SearchRequest extends Request
{
    use RequestValidate;

    /**
     * @inheritDoc
     * @param $keys
     * @return array
     */
    public function all($keys = null): array
    {
        return $this->getConditions($keys);
    }


    /**
     * Get all valid conditions from the request
     *
     * @param array|mixed|null $keys
     * @return array
     */
    public function getConditions(mixed $keys = null): array
    {
        return collect(parent::all($keys))
            ->except(static::reservedKeys())
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
