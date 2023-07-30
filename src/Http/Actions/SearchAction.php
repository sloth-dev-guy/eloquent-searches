<?php

namespace SlothDevGuy\Searches\Http\Actions;

use CTDesarrollo\PerformanceMonitor\Models\Model;
use SlothDevGuy\Searches\Http\Requests\SearchRequest;
use SlothDevGuy\Searches\Interfaces\ItemResponseSchemaInterface as ItemResponse;
use SlothDevGuy\Searches\Interfaces\SearchResponseSchemaInterface as SearchResponse;
use SlothDevGuy\Searches\ResponseModels\ItemResponseSchema;
use SlothDevGuy\Searches\ResponseModels\SearchResponseSchema;
use SlothDevGuy\Searches\Searcher;

class SearchAction
{
    /**
     * @var string
     */
    protected static string $defaultResponseClass = SearchResponseSchema::class;

    /**
     * @var string
     */
    protected static string $defaultResponseItemClass = ItemResponseSchema::class;

    /**
     * @var SearchResponse
     */
    protected SearchResponse $response;

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var Model
     */
    protected Model $from;

    /**
     * @param SearchRequest $request
     * @param array $options
     */
    public function __construct(
        protected SearchRequest $request,
        array $options = [],
    )
    {
        $this->options(array_merge($this->options, $options));
    }

    /**
     * @param array|null $options
     * @return array
     */
    public function options(array $options = null): array
    {
        if(!is_null($options)){
            $this->options = $options;
        }

        return $this->options;
    }

    /**
     * Returns the request object
     *
     * @return SearchRequest
     */
    public function request(): SearchRequest
    {
        return $this->request;
    }

    /**
     * Gets or sets the response schema returned by this action
     *
     * @param SearchResponse|null $response
     * @return SearchResponse
     */
    public function response(SearchResponse $response = null): SearchResponse
    {
        if(!is_null($response)){
            $this->response = $response;
        }

        return $this->response;
    }

    /**
     * Gets or sets the form model instance from with the query builder will be instanced
     *
     * @param Model|null $from
     * @return Model
     */
    public function from(Model $from = null): Model
    {
        if(!is_null($from)){
            $this->from = $from;
        }

        return $this->from;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $from = $this->from();
        $conditions = $this->request()->getConditions();
        $options = $this->options();
        $class = class_basename($this);

        logger()->debug("$class::execute search", compact('from', 'conditions', 'options'));

        $search = eloquent_search($from, $conditions, $options);

        $this->response(static::buildSearchResponse($search, $this->options()));
    }

    /**
     * Build a new search response
     *
     * @param Searcher $search
     * @param array $options
     * @return SearchResponse
     */
    public static function buildSearchResponse(Searcher $search, array $options): SearchResponse
    {
        /** @var SearchResponseSchema $responseClass */
        $responseClass = static::$defaultResponseClass;

        return $responseClass::fromSearch($search)
            ->setItemResponse(static::buildItemResponse($options));
    }

    /**
     * Builds a new item response
     *
     * @param array $options
     * @return ItemResponse
     */
    public static function buildItemResponse(array $options): ItemResponse
    {
        $responseItemClass = static::$defaultResponseItemClass;

        return new $responseItemClass();
    }
}
