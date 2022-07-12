<?php

namespace SlothDevGuy\Searches\Interfaces;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use SlothDevGuy\Searches\Searcher;

/**
 * Interface SearchResponseInterfaces
 * @package SlothDevGuy\Searches\Interfaces
 */
interface SearchResponseInterfaces extends Jsonable, Arrayable
{
    /**
     * @param bool $force
     * @return SearchResponseInterfaces
     */
    public function forcePaginationResponse(bool $force = true) : SearchResponseInterfaces;

    /**
     * @param ItemResponseInterface $itemResponse
     * @return SearchResponseInterfaces
     */
    public function setItemResponse(ItemResponseInterface $itemResponse) : SearchResponseInterfaces;

    /**
     * @return array
     */
    public function map() : array;

    /**
     * @return Searcher
     */
    public function search() : Searcher;

    /**
     * @param Searcher $search
     * @return SearchResponseInterfaces
     */
    public static function builtFromSearch(Searcher $search) : SearchResponseInterfaces;
}
