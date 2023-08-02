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
        mixed              $values,
        array              $options = [],
    )
    {
        $this->arguments = new WhereArguments($this->fieldWithArguments, $values, $options);

        $this->pickWhereBuilder();

        $this->setValuesInWhere($this->castValuesIfFieldRequires($values));
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
    protected function pickWhereBuilder(): static
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
     * @param mixed $values
     * @return mixed
     */
    public function castValuesIfFieldRequires(mixed $values): mixed
    {
        if(!isset($this->where)){
            return null;
        }

        $arguments = $this->where->arguments();
        if(!$arguments->option('cast')){
            return null;
        }

        $field = $arguments->option('cast') === true? $arguments->field() : $arguments->option('cast.0');
        $from = $this->searcher()->from();
        $model = new $from;

        if(is_string($values)){
            $model->setAttribute($field, $values);
            $castValue = $model->getAttributeValue($field);

            if(is_object($castValue) && method_exists($castValue, 'cast')){
                $castValue = $castValue->cast();
            }

            return $castValue;
        }

        if(is_array($values)){
            return array_map(fn($value) => $this->castValuesIfFieldRequires($value), $values);
        }

        return $values;
    }

    /**
     * @param mixed $values
     * @return void
     */
    protected function setValuesInWhere(mixed $values): void
    {
        if(!is_null($values) && isset($this->where)){
            $this->where->arguments()->value($values);
        }
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
