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
     * @param mixed $conditions
     * @param Builder|null $builder
     * @param array $options
     */
    public function __construct(
        protected Model $from,
        protected       $conditions,
        Builder         $builder = null,
        array $options = [],
    )
    {
        $this->options = static::defaultOptions($options);

        $this->builder = $builder ?? $this->from()->newQuery();

        $this->select($this->option('select', $this->getFromQualifiedField('*')));
    }

    /**
     * @return Model
     */
    public function from() : Model
    {
        return $this->from;
    }

    /**
     * @return mixed
     */
    public function conditions()
    {
        return $this->conditions;
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
    public function get(int $max = null, int $page = null)
    {
        $builder = $this->builder()->clone();

        $builder->select($this->select());

        $max = $max ?? request()->query('max');

        if($max > 0){
            $builder->take($max);
            $this->pagination['max'] = $max;
            $page = $page ?? request()->query('page', 1);
º
            if($page > 0){
                $builder->offset(($page - 1) * $max);
                $this->pagination['page'] = $page;
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
        $builder = $this->builder()->clone();

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
        return $this->pagination;
    }

    /**
     * @return Builder
     */
    public function builder() : Builder
    {
        //id?
        $conditions = collect($this->conditions);

        $conditions = $conditions->map($this->mapSearchBuilders());

        //fail if some argument is missing or can't be handled?

        $conditions->each(fn(SearchBuilder $searchBuilder) => $searchBuilder->pushInQueryBuilder($this->builder));

        return $this->builder;
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
