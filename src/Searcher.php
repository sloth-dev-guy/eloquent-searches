<?php

namespace SlothDevGuy\Searches;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface Searcher
 * @package SlothDevGuy\Searches
 */
interface Searcher
{
    /**
     * @return Model
     */
    public function from() : Model;

    /**
     * @param $select
     * @return mixed
     */
    public function select($select = null);

    /**
     * @return mixed
     */
    public function get();

    /**
     * @param int $max
     * @param callable $closure
     * @return mixed
     */
    //public function chuck(int $max, callable $closure);

    /**
     * @param bool $reCount
     * @return int
     */
    public function count(bool $reCount = false) : int;

    /**
     * @return array
     */
    public function pagination() : array;

    /**
     * @return Builder
     */
    public function builder() : Builder;

    /**
     * @param string $field
     * @return string
     */
    public function getFromQualifiedField(string $field) : string;

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function option(string $key, $default = null);

    /**
     * @return array
     */
    public function options() : array;

    /**
     * @param array $options
     * @return array
     */
    public static function defaultOptions(array $options = []) : array;

    /**
     * @param string $field
     * @param string $table
     * @param string|null $alias
     * @return string
     */
    public static function getQualifiedField(string $field, string $table, string $alias = null) : string;
}
