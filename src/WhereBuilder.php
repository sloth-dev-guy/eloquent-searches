<?php

namespace SlothDevGuy\Searches;

use SlothDevGuy\Searches\Where\WhereArguments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface WhereBuilder
 * @package SlothDevGuy\Searches
 */
interface WhereBuilder
{
    /**
     * @return WhereBuilder
     */
    public function redirect() : WhereBuilder;

    /**
     * @return Model
     */
    public function from() : Model;

    /**
     * @param string $field
     * @return string
     */
    public function getQualifiedField(string $field) : string;

    /**
     * @return array
     */
    public function where() : array;

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function pushInBuilder(Builder $builder) : Builder;

    /**
     * @return WhereArguments
     */
    public function arguments() : WhereArguments;

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function option(string $key, $default = null);

    /**
     * @param string $method
     * @return bool
     */
    public static function supportsMethod(string $method) : bool;
}
