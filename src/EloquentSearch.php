<?php

namespace SlothDevGuy\Searches;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait EloquentSearch
 * @package SlothDevGuy\Searches
 * @mixin Model
 */
trait EloquentSearch
{
    /**
     * @param mixed $conditions
     * @param array $options
     * @return Searcher
     */
    public function newSearch(mixed $conditions, array $options = []) : Searcher
    {
        return eloquent_search($this, $conditions, $options);
    }

    /**
     * @param mixed $conditions
     * @param array $options
     * @return Searcher
     */
    public static function search(mixed $conditions, array $options = []) : Searcher
    {
        return (new static())->newSearch($conditions, $options);
    }
}
