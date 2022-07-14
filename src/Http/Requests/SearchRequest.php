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

    public function all($keys = null)
    {
        return collect(parent::all($keys))->except(['page', 'max', 'distinct'])
            ->toArray();
    }
}
