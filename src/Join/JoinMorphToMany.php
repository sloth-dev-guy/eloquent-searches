<?php

namespace SlothDevGuy\Searches\Join;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;

/**
 * Class MorphToManyRelationship
 * @package SlothDevGuy\Searches\Join
 */
class JoinMorphToMany extends JoinRelationship
{
    /**
     * @param Model $from
     * @param MorphToMany $relationship
     * @param array $options
     */
    public function __construct(
        protected Model $from,
        protected MorphToMany $relationship,
        array $options = [],
    )
    {
        $options['pivot_table'] = $this->relationship->getTable();

        $this->options = static::defaultOptions($options);
    }

    /**
     * @return array[]
     */
    public function joins() : array
    {
        $relationship = $this->relationship;

        $pivotAlias = $this->options['pivot_table_alias'];
        $pivotJoinContext = $pivotAlias?
            "{$this->relationship->getTable()} as {$pivotAlias}" :
            $this->relationship->getTable();

        $pivotJoin = [
            'table' => $pivotJoinContext,
            'arguments' => [
                $this->joinClosure(),
            ],
        ];

        $first = $this->getToTableQualifiedField($relationship->getParentKeyName());
        $operator = $this->joinOperator();
        $second = $this->getPivotTableQualifiedField($relationship->getRelatedPivotKeyName());

        $baseJoin = [
            'table' => $this->getJoinContext(),
            'arguments' => [$first, $operator, $second],
        ];

        return [$pivotJoin, $baseJoin];
    }

    /**
     * @return Model
     */
    public function to() : Model
    {
        return $this->relationship->getModel();
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
    public function on() : array
    {
        $relationship = $this->relationship;

        $first = $this->getFromTableQualifiedField($relationship->getParentKeyName());
        $operator = $this->joinOperator();
        $second = $this->getPivotTableQualifiedField($relationship->getForeignPivotKeyName());

        return compact('first', 'operator', 'second');
    }

    /**
     * @return array
     */
    public function wheres() : array
    {
        $relationship = $this->relationship;

        $first = $this->getPivotTableQualifiedField($relationship->getMorphType());
        $operator = $this->joinOperator();
        $second = $this->getModelClass();

        return [
            compact('first', 'operator', 'second'),
        ];
    }

    /**
     * @return string
     */
    public function getModelClass() : string
    {
        return get_class($this->from());
    }

    public function getPivotTableQualifiedField(string $field) : string
    {
        return static::getQualifiedField($field, $this->relationship->getTable(), $this->options['pivot_table_alias']);
    }

    /**
     * @param Relation $relationship
     * @return bool
     */
    public static function instanceOf(Relation $relationship) : bool
    {
        return $relationship instanceof MorphToMany;
    }

    /**
     * @param array $options
     * @return array
     */
    protected static function defaultOptions(array $options = []): array
    {
        $options = parent::defaultOptions($options);

        $alias = data_get($options, 'to_table_alias');

        if($alias){
            $table = data_get($options, 'pivot_table');

            $options['pivot_table_alias'] = "{$table}_{$alias}";
        }

        return $options;
    }
}
