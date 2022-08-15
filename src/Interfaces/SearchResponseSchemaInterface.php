<?php

namespace SlothDevGuy\Searches\Interfaces;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use SlothDevGuy\Searches\Searcher;

/**
 * Interface SearchResponseInterfaces
 * @package SlothDevGuy\Searches\Interfaces
 */
interface SearchResponseSchemaInterface extends Jsonable, Arrayable
{
    /**
     * @param bool $force
     * @return SearchResponseSchemaInterface
     */
    public function forcePaginationResponse(bool $force = true) : SearchResponseSchemaInterface;

    /**
     * @param ItemResponseSchemaInterface $itemResponse
     * @return SearchResponseSchemaInterface
     */
    public function setItemResponse(ItemResponseSchemaInterface $itemResponse) : SearchResponseSchemaInterface;

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
     * @return SearchResponseSchemaInterface
     */
    public static function fromSearch(Searcher $search) : SearchResponseSchemaInterface;
}
