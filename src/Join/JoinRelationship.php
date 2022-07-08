<?php

namespace SlothDevGuy\Searches\Join;

use Closure;
use Illuminate\Database\Query\JoinClause;
use SlothDevGuy\Searches\JoinRelationshipBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class BaseJoinAdapter
 * @package SlothDevGuy\Searches\Join
 *
 * @property-read Relation $relationship
 */
abstract class JoinRelationship implements JoinRelationshipBuilder
{
    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var Model
     */
    protected Model $from;

    /**
     * @param array $options
     * @return array
     */
    protected static function defaultOptions(array $options = []) : array
    {
        return array_merge([
            'method' => 'join',
            'join_operator' => '=',
            'from_table_alias' => null,
            'to_table_alias' => null,
        ], $options);
    }

    /**
     * @return Model
     */
    public function from() : Model
    {
        return $this->from;
    }

    /**
     * @param string $field
     * @return string
     */
    public function getFromTableQualifiedField(string $field) : string
    {
        return static::getQualifiedField($field, $this->from()->getTable(), $this->options['from_table_alias']);
    }

    /**
     * @return Model
     */
    public function to() : Model
    {
        return $this->relationship->getModel();
    }

    /**
     * @return array[]
     */
    public function joins() : array
    {
        return [
            $this->baseJoin()
        ];
    }

    /**
     * @return array|Closure[]
     */
    protected function baseJoin()
    {
        $join = [
            'table' => $this->getJoinContext(),
            'arguments' => array_values($this->on()),
        ];

        if(!empty($this->wheres())){
            $join['arguments'] = $this->joinClosure();
        }

        return $join;
    }

    /**
     * @return Closure
     */
    protected function joinClosure()
    {
        return function (JoinClause $join) {
            $on = $this->on();
            $join->on(...array_values($on));

            foreach ($this->wheres() as $where){
                $join->where(...array_values($where));
            }
        };
    }

    /**
     * @return array
     */
    public function wheres(): array
    {
        return [];
    }

    /**
     * @param string $field
     * @return string
     */
    public function getToTableQualifiedField(string $field) : string
    {
        return static::getQualifiedField($field, $this->to()->getTable(), $this->options['to_table_alias']);
    }

    /**
     * @return string
     */
    public function joinOperator() : string
    {
        return data_get($this->options, 'join_operator', '=');
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function pushInBuilder(Builder $builder) : Builder
    {
        $joins = $this->joins();

        foreach ($joins as $join){
            $table = $join['table'];
            $arguments = array_values($join['arguments']);
            $method = $this->options['method'];

            $builder->{$method}($table, ...$arguments);
        }

        return $builder;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return !empty($this->joins());
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return array|mixed
     */
    public function option(string $key, $default = null)
    {
        return data_get($this->options, $key, $default);
    }

    /**
     * @return string
     */
    protected function getJoinContext() : string
    {
        $alias = $this->options['to_table_alias'];

        return $alias?
            "{$this->to()->getTable()} as {$alias}" :
            $this->to()->getTable();
    }

    /**
     * @param string $field
     * @param string $table
     * @param string|null $alias
     * @return string
     */
    public static function getQualifiedField(string $field, string $table, string $alias = null) : string
    {
        $alias = $alias? : $table;

        return str_contains($field, '.')?
            $field :
            "{$alias}.{$field}";
    }
}
