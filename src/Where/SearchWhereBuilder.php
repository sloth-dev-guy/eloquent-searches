<?php

namespace SlothDevGuy\Searches\Where;

use SlothDevGuy\Searches\SearchBuilder;
use SlothDevGuy\Searches\Searcher;
use SlothDevGuy\Searches\WhereBuilder;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class SearchWhereBuilder
 * @package SlothDevGuy\Searches\Where
 */
class SearchWhereBuilder implements SearchBuilder
{
    /**
     * @var array|WhereBuilder[]
     */
    protected static $supportedWheres = [
        Where::class,
        WhereNull::class,
        WhereColumn::class,
        WhereIn::class,
        WhereBetween::class,
        WhereFullText::class,
        //WhereNest::class, //is not necessary it will be redirected by the where strategy
    ];

    /**
     * @var WhereArguments
     */
    protected WhereArguments $arguments;

    /**
     * @var WhereBuilder|null
     */
    protected ?WhereBuilder $where = null;

    /**
     * @param Searcher $searcher
     * @param string $fieldWithArguments
     * @param mixed $values
     * @param array $options
     */
    protected function __construct(
        protected Searcher $searcher,
        protected string   $fieldWithArguments,
                           $values,
        array              $options = [],
    )
    {
        $this->arguments = new WhereArguments($this->fieldWithArguments, $values, $options);

        $this->pickWhereBuilder();
    }

    /**
     * @return Searcher
     */
    public function searcher(): Searcher
    {
        return $this->searcher;
    }

    public function arguments() : WhereArguments
    {
        return $this->arguments;
    }

    public function where(): WhereBuilder|null
    {
        return $this->where;
    }

    /**
     * @return $this
     */
    protected function pickWhereBuilder()
    {
        $method = $this->arguments()->method();

        $where = collect(static::$supportedWheres)
            ->first(fn($whereBuilder) => /** @var WhereBuilder $whereBuilder */ $whereBuilder::supportsMethod($method));

        if($where){
            $this->where = new $where($this);

            //maybe to selected where method has to redirect to another where class
            $this->where = $this->where()->redirect();
        }

        return $this;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function pushInQueryBuilder(Builder $builder): Builder
    {
        if (!$this->where()) {
            return $builder;
        }

        return $this->where()->pushInBuilder($builder);
    }

    /**
     * @param Searcher $search
     * @param string $key
     * @param mixed $value
     * @param array $options
     * @return SearchBuilder|null
     */
    public static function buildFromKeyAndValue(Searcher $search, string $key, $value, array $options = []): SearchBuilder|null
    {
        $builder = new static($search, $key, $value, $options);

        if (!$builder->where()) {
            return null;
        }

        return $builder;
    }
}
