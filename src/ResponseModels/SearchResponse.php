<?php

namespace SlothDevGuy\Searches\ResponseModels;

use SlothDevGuy\Searches\Interfaces\ItemResponseInterface;
use SlothDevGuy\Searches\Interfaces\SearchResponseInterfaces;
use SlothDevGuy\Searches\Searcher;

/**
 * Class SearchResponse
 * @package SlothDevGuy\Searches\ResponseModels
 */
class SearchResponse implements SearchResponseInterfaces
{
    /**
     * @var bool
     */
    protected bool $forcePagination = false;

    /**
     * @var array
     */
    protected array $map = [];

    /**
     * @param Searcher $search
     * @param ItemResponseInterface $itemResponse
     */
    public function __construct(
        protected Searcher $search,
        protected ItemResponseInterface $itemResponse
    )
    {

    }

    /**
     * @return Searcher
     */
    public function search(): Searcher
    {
        return $this->search;
    }

    /**
     * @return array
     */
    public function map(): array
    {
        if(empty($this->map)){
            $this->map = collect($this->search()->get())
                ->each($this->itemResponse::mapEach())
                ->toArray();
        }

        return $this->map;
    }

    /**
     * @param bool $force
     * @return SearchResponseInterfaces
     */
    public function forcePaginationResponse(bool $force = true): SearchResponseInterfaces
    {
        $this->forcePagination = $force;

        return $this;
    }

    /**
     * @param ItemResponseInterface $itemResponse
     * @return SearchResponseInterfaces
     */
    public function setItemResponse(ItemResponseInterface $itemResponse): SearchResponseInterfaces
    {
        $this->itemResponse = $itemResponse;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->withPagination()?
            $this->mapWithPagination() :
            $this->map();
    }

    /**
     * @return bool
     */
    protected function withPagination() : bool
    {
        $pagination = $this->search()->pagination();
        $page = data_get($pagination, 'page');

        return $page > 0 || $this->forcePagination;
    }

    /**
     * @return array
     */
    protected function mapWithPagination() : array
    {
        $pagination = $this->search()->pagination();

        $mapWithPagination = [];

        foreach ($pagination as $key => $value){
            $newKey = config("searches.responses.pagination_keys.{$key}", $key);
            $mapWithPagination[$newKey] = $value;
        }

        $itemKey = config('searches.responses.pagination_keys.items', 'items');
        $mapWithPagination[$itemKey] = $this->map();

        return $mapWithPagination;
    }

    /**
     * @param int $options
     * @return false|string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @param Searcher $search
     * @return SearchResponseInterfaces
     */
    public static function builtFromSearch(Searcher $search): SearchResponseInterfaces
    {
        return new static($search, new ItemResponse());
    }
}
