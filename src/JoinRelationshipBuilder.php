<?php

namespace SlothDevGuy\Searches;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Interface JoinBuilderAdapter
 * @package SlothDevGuy\Searches
 */
interface JoinRelationshipBuilder
{
    /**
     * @return Model
     */
    public function from() : Model;

    /**
     * @param string $field
     * @return string
     */
    public function getFromTableQualifiedField(string $field) : string;

    /**
     * @return Model
     */
    public function to() : Model;

    /**
     * @param string $field
     * @return string
     */
    public function getToTableQualifiedField(string $field) : string;

    /**
     * @return array
     */
    public function joins() : array;

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function option(string $key, $default = null);

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function pushInBuilder(Builder $builder) : Builder;

    /**
     * @param Relation $relationship
     * @return mixed
     */
    public static function instanceOf(Relation $relationship) : bool;

    /**
     * @param string $field
     * @param string $table
     * @param string|null $alias
     * @return string
     */
    public static function getQualifiedField(string $field, string $table, string $alias = null) : string;
}
