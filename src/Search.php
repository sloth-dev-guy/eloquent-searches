<?php

namespace SlothDevGuy\Searches;

use Closure;
use SlothDevGuy\Searches\Join\SearchJoinRelationshipBuilder;
use SlothDevGuy\Searches\Where\SearchWhereBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class Search
 * @package SlothDevGuy\Searches
 */
class Search implements Searcher
{
    use BuilderOptions;

    /**
     * @var Builder
     */
    protected Builder $fromBuilder;

    /**
     * @var Builder
     */
    protected Builder $builder;

    /**
     * @var \Illuminate\Support\Collection|SearchBuilder[]
     */
    protected $conditions;

    /**
     * @var null
     */
    protected $select = null;

    /**
     * @var Builder
     */
    protected Builder $lastBuilder;

    /**
     * @var array|int[]
     */
    protected array $pagination = [
        'page' => null,
        'pages' => null,
        'max' => null,
        'total' => null,
    ];

    /**
     * @var string[]|SearchBuilder[]
     */
    protected array $builders = [
        SearchJoinRelationshipBuilder::class,
        SearchWhereBuilder::class,
    ];

    /**
     * @param Model $from
     * @param mixed $rawConditions
     * @param Builder|null $builder
     * @param array $options
     */
    public function __construct(
        protected Model $from,
        protected mixed $rawConditions,
        Builder         $builder = null,
        array           $options = [],
    )
    {
        $this->conditions = collect();
        $this->options = static::defaultOptions($options);

        $this->fromBuilder = $builder ?? $this->from()->newQuery();

        $this->select($this->option('select', $this->getFromQualifiedField('*')));

        $this->pagination['max'] = $this->option('max');
        $this->pagination['page'] = $this->option('page');
    }

    /**
     * @return Model
     */
    public function from() : Model
    {
        return $this->from;
    }

    /**
     * @param string|array $select
     * @return array|string|null
     */
    public function select($select = null)
    {
        if(!is_null($select)){
            $this->select = $select;
        }

        return $this->select;
    }

    /**
     * @inheritdoc
     * @param bool|null $distinct
     * @return bool
     */
    public function distinct(bool $distinct = null) : bool
    {
        if(!is_null($distinct)){
            $this->options['distinct'] = $distinct;
        }

        return $this->option('distinct', false);
    }

    /**
     *
     * @return Builder[]|Collection
     */
    public function get()
    {
        $builder = $this->builder();

        $this->selectIn($builder);

        $this->distinctIn($builder);

        //$this->aggregateIn($builder);

        $this->orderByIn($builder);

        $this->groupByIn($builder);

        //$this->havingIn($builder);

        $this->paginateIn($builder);

        $this->lastBuilder = $builder;

        return $builder->get();
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function selectIn(Builder $builder)
    {
        $builder->select($this->select());

        return $builder;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function distinctIn(Builder $builder)
    {
        $this->distinct() && $builder->distinct();

        return $builder;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function aggregateIn(Builder $builder)
    {
        return $builder;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function orderByIn(Builder $builder)
    {
        collect($this->option('order'))
            ->filter()
            ->each(fn($order) => $builder->orderBy(...explode(',', $order)));

        return $builder;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function groupByIn(Builder $builder)
    {
        $groups = array_filter(array_map('trim', (array) $this->option('group')));

        $builder->groupBy(...$groups);

        return $builder;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function havingIn(Builder $builder)
    {
        return $builder;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function paginateIn(Builder $builder)
    {
        $max = $this->pagination['max'];

        if($max > 0){
            $builder->take($max);
            $page = $this->pagination['page'];

            if($page > 0){
                $builder->offset(($page - 1) * $max);
            }
        }

        return $builder;
    }

    /**
     * @param bool $reCount
     * @return int
     */
    public function count(bool $reCount = false) : int
    {
        $builder = $this->builder();

        if(is_null($this->pagination['total']) || $reCount){
            $this->pagination['total'] = $builder->count();

            if($this->pagination['max']) {
                $this->pagination['pages'] = ceil($this->pagination['total'] / $this->pagination['max']);
            }
        }

        return $this->pagination['total'];
    }

    /**
     * @return array|int[]
     */
    public function pagination() : array
    {
        if(is_null($this->pagination['total'])){
            $this->count();
        }

        return $this->pagination;
    }

    /**
     * @return Builder
     */
    public function builder() : Builder
    {
        //@note: to prevent double condition evaluations witch can provoke a double negation error
        //we only evaluate the conditions once
        if(!isset($this->builder)){
            //id?
            if($this->conditions->isEmpty()) {
                $this->conditions = collect($this->rawConditions)
                    ->map($this->mapSearchBuilders());
            }

            $builder = $this->fromBuilder->clone();

            foreach ($this->conditions as $condition){
                $builder = $condition->pushInQueryBuilder($builder);
            }

            $this->builder = $builder;
        }

        return $this->builder;
    }

    /**
     * @param string $field
     * @return string
     */
    public function getFromQualifiedField(string $field): string
    {
        if(!$this->option('qualified_fields', true)){
            return $field;
        }

        return static::getQualifiedField($field, $this->from()->getTable(), $this->option('from_alias'));
    }

    /**
     * @return Closure
     */
    protected function mapSearchBuilders()
    {
        return function ($values, $key){
            $builder = null;

            foreach ($this->builders as $searchBuilder){
                if($builder = $searchBuilder::buildFromKeyAndValue($this, $key, $values)){
                    return $builder;
                }
            }

            if(!$builder){
                Log::warning('search, map-search-builders, unsupported-key-or-value, return-null-builder', [
                    'key' => $key,
                    'value' => $values,
                    'from' => get_class($this->from()),
                ]);
            }

            return $builder;
        };
    }

    /**
     * @param string $field
     * @param string $table
     * @param string|null $alias
     * @return string
     */
    public static function getQualifiedField(string $field, string $table, string $alias = null): string
    {
        $alias = $alias? : $table;

        return str_contains($field, '.')?
            $field :
            "{$alias}.{$field}";
    }

    /**
     * @param array $options
     * @return array
     */
    public static function defaultOptions(array $options = []): array
    {
        //@todo options must be injected from the action through the request
        return array_merge([
            'distinct' => (bool) request()->query('distinct', false),
            'max' => request()->query('max'),
            'page' => request()->query('page'),
            'order' => request()->query('order'),
            'group' => request()->query('group'),
        ], $options);
    }

    /**
     * @return Builder
     */
    public function getLastBuilder(): Builder
    {
        return $this->lastBuilder;
    }
}
