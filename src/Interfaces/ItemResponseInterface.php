<?php

namespace SlothDevGuy\Searches\Interfaces;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface ItemResponseInterface
 * @package SlothDevGuy\Searches\Interfaces
 */
interface ItemResponseInterface extends Jsonable, Arrayable
{
    /**
     * @return mixed
     */
    public function item();

    /**
     * @return Closure
     */
    public static function mapEach() : Closure;

    /**
     * @return array
     */
    public function map() : array;
}
