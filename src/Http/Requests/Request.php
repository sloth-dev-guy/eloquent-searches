<?php

namespace SlothDevGuy\Searches\Http\Requests;

use Illuminate\Http\Request as BaseRequest;

/**
 * Class Request
 * @package SlothDevGuy\Searches\Http\Requests
 */
class Request extends BaseRequest
{
    use RequestValidate;
}
