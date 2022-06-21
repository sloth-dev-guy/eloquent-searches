<?php

namespace SlothDevGuy\Searches\Where;

use SlothDevGuy\Searches\WhereBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class BaseWhere
 * @package SlothDevGuy\Searches\Where
 */
abstract class BaseWhere implements WhereBuilder
{
    /**
     * @var array
     */
    protected static array $supportedMethods = [];

    /**
     * @param SearchWhereBuilder $search
     */
    public function __construct(
        protected SearchWhereBuilder $search
    )
    {

    }

    /**
     * @return BaseWhere
     */
    public function redirect() : BaseWhere
    {
        return $this;
    }

    /**
     * @return SearchWhereBuilder
     */
    public function searchBuilder() : SearchWhereBuilder
    {
        return $this->search;
    }

    /**
     * @return Model
     */
    public function from(): Model
    {
        return $this->searchBuilder()
            ->searcher()
            ->from();
    }

    /**
     * @param string $field
     * @return string
     */
    public function getQualifiedField(string $field): string
    {
        return $this->searchBuilder()
            ->searcher()
            ->getFromQualifiedField($field);
    }

    /**
     * @return WhereArguments
     */
    public function arguments(): WhereArguments
    {
        return $this->searchBuilder()->arguments();
    }

    /**
     * @param string $key
     * @param $default
     * @return array|mixed
     */
    public function option(string $key, $default = null)
    {
        return $this->arguments()->option($key, $default);
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function pushInBuilder(Builder $builder): Builder
    {
        $where = $this->where();

        $method = $where['method'];

        if(!$method){
            $class = class_basename($this);

            Log::warning("$class, invalid-method", $where);

            return $builder;
        }

        $arguments = array_values($where['arguments']);

        $builder->{$method}(...$arguments);

        return $builder;
    }

    /**
     * @return $this
     */
    protected function syncMethodWithOperand() : static
    {
        $arguments = $this->arguments();

        //check if we need to change the method related to the operand
        if($arguments->operand() === 'or' && !Str::startsWith($arguments->method(), 'or')){
            //only add the or at the begging and capitalize the first letter witch will be the where
            $arguments->method('or' . ucfirst($arguments->method()));
        }
        elseif($arguments->operand() === 'and' && Str::startsWith($arguments->method(), 'or')){
            //only add the or at the begging and capitalize the first letter witch will be the where
            $arguments->method(str_replace('orW', 'w', $arguments->method()));
        }

        return $this;
    }

    /**
     * @return static
     */
    abstract protected function negateIfRequired();

    /**
     * @param string $method
     * @return bool
     */
    public static function supportsMethod(string $method): bool
    {
        return in_array($method, static::$supportedMethods);
    }
}
