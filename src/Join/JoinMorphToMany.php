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
                $this->joinPivotClosure(),
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
     * @param Relation $relationship
     * @return bool
     */
    public static function instanceOf(Relation $relationship) : bool
    {
        return $relationship instanceof MorphToMany;
    }

    /**
     * @return Closure
     */
    protected function joinPivotClosure()
    {
        return function (JoinClause $join) {
            $relationship = $this->relationship;

            $first = $this->getFromTableQualifiedField($relationship->getParentKeyName());
            $operator = $this->joinOperator();
            $second = $this->getPivotTableQualifiedField($relationship->getForeignPivotKeyName());

            $join->on($first, $operator, $second);

            $first = $this->getPivotTableQualifiedField($relationship->getMorphType());
            $operator = $this->joinOperator();
            $second = $this->getModelClass();

            $join->where($first, $operator, $second);
        };
    }

    /**
     * @return string
     */
    protected function getModelClass() : string
    {
        return get_class($this->from());
    }

    public function getPivotTableQualifiedField(string $field) : string
    {
        return static::getQualifiedField($field, $this->relationship->getTable(), $this->options['pivot_table_alias']);
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
