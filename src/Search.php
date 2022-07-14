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
        protected       $rawConditions,
        Builder         $builder = null,
        array           $options = [],
    )
    {
        $this->conditions = collect();
        $this->options = static::defaultOptions($options);

        $this->builder = $builder ?? $this->from()->newQuery();

        $this->select($this->option('select', $this->getFromQualifiedField('*')));

        $this->pagination['max'] = $this->option('max', request()->query('max'));
        $this->pagination['page'] = $this->option('page', request()->query('page'));
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
     *
     * @return Builder[]|Collection
     */
    public function get()
    {
        $builder = $this->builder();

        $builder->select($this->select());

        $max = $this->pagination['max'];

        if($max > 0){
            $builder->take($max);
            $page = $this->pagination['page'];

            if($page > 0){
                $builder->offset(($page - 1) * $max);
            }
        }

        return $builder->get();
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
        //id?
        if($this->conditions->isEmpty()) {
            $this->conditions = collect($this->rawConditions)
                ->map($this->mapSearchBuilders());
        }

        $builder = $this->builder->clone();

        foreach ($this->conditions as $condition){
            $builder = $condition->pushInQueryBuilder($builder);
        }

        return $builder;
    }

    /**
     * @param string $field
     * @return string
     */
    public function getFromQualifiedField(string $field): string
    {
        return static::getQualifiedField($field, $this->from()->getTable(), $this->option('from_alias'));
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
}
