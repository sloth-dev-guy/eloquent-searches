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
interface ItemResponseSchemaInterface extends Jsonable, Arrayable
{
    /**
     * @return mixed
     */
    public function item();

    /**
     * @return array
     */
    public function map() : array;

    /**
     * @param array|null $keys
     * @return ItemResponseSchemaInterface
     */
    public function only(array $keys = null) : ItemResponseSchemaInterface;

    /**
     * @param array|null $keys
     * @return ItemResponseSchemaInterface
     */
    public function except(array $keys = null) : ItemResponseSchemaInterface;

    /**
     * @return Closure
     */
    public static function mapEach() : Closure;
}
