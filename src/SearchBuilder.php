<?php

namespace SlothDevGuy\Searches;

use Illuminate\Database\Eloquent\Builder;

/**
 * Interface SearchBuilder
 * @package SlothDevGuy\Searches
 */
interface SearchBuilder
{
    /**
     * @param Searcher $search
     * @param string $key
     * @param $value
     * @param array $options
     * @return SearchBuilder|null
     */
    public static function buildFromKeyAndValue(Searcher $search, string $key, $value, array $options = []) : SearchBuilder|null;

    /**
     * @param Builder $builder
     * @return mixed
     */
    public function pushInQueryBuilder(Builder $builder) : Builder;
}
