<?php

namespace SlothDevGuy\Searches\ResponseModels;

use SlothDevGuy\Searches\Interfaces\ItemResponseSchemaInterface;
use SlothDevGuy\Searches\Interfaces\SearchResponseSchemaInterface;
use SlothDevGuy\Searches\Searcher;

/**
 * Class SearchResponse
 * @package SlothDevGuy\Searches\ResponseModels
 */
class SearchResponseSchema implements SearchResponseSchemaInterface
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
     * @param ItemResponseSchemaInterface $itemResponse
     */
    public function __construct(
        protected Searcher $search,
        protected ItemResponseSchemaInterface $itemResponse
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
                ->map($this->itemResponse::mapEach())
                ->toArray();
        }

        return $this->map;
    }

    /**
     * @param bool $force
     * @return SearchResponseSchemaInterface
     */
    public function forcePaginationResponse(bool $force = true): SearchResponseSchemaInterface
    {
        $this->forcePagination = $force;

        return $this;
    }

    /**
     * @param ItemResponseSchemaInterface $itemResponse
     * @return SearchResponseSchemaInterface
     */
    public function setItemResponse(ItemResponseSchemaInterface $itemResponse): SearchResponseSchemaInterface
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

            //all pagination values should be integer values
            $mapWithPagination[$newKey] = (int) $value;
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
     * @return SearchResponseSchemaInterface
     */
    public static function fromSearch(Searcher $search): SearchResponseSchemaInterface
    {
        return new static($search, new ItemResponseSchema());
    }
}
