<?php

namespace SlothDevGuy\Searches\Join;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class JoinBelongsToMany
 * @package SlothDevGuy\Searches\Join
 */
class JoinBelongsToMany extends JoinRelationship
{
    /**
     * @param Model $from
     * @param BelongsToMany $relationship
     * @param array $options
     */
    public function __construct(
        protected Model $from,
        protected BelongsToMany $relationship,
        array $options = [],
    )
    {
        $this->options = static::defaultOptions($options);
    }

    /**
     * @return array[]
     */
    public function joins() : array
    {
        return [
            $this->pivotJoin(),
            $this->baseJoin()
        ];
    }

    /**
     * @return array
     */
    public function pivotJoin()
    {
        //@todo rename table key for join_context, and arguments for join_arguments
        return [
            'table' => $this->getPivotJoinContext(),
            'arguments' => array_values($this->onPivot()),
        ];
    }

    /**
     * @return array
     */
    public function onPivot() : array
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
    public function on(): array
    {
        $relationship = $this->relationship;

        $first = $this->getToTableQualifiedField($relationship->getRelatedKeyName());
        $operator = $this->joinOperator();
        $second = $this->getPivotTableQualifiedField($relationship->getRelatedPivotKeyName());

        return compact('first', 'operator', 'second');
    }

    /**
     * @return string
     */
    public function getPivotJoinContext() : string
    {
        $pivotTable = $this->relationship->getTable();
        $fromAlias = $this->option('from_table_alias');
        $toAlias = $this->option('to_table_alias');

        $context = collect([$fromAlias, $toAlias])->filter();

        return $context->count() > 0?
            "{$pivotTable} as {$pivotTable}_{$context->implode('_')}" :
            $pivotTable;
    }

    /**
     * @param string $field
     * @return string
     */
    public function getPivotTableQualifiedField(string $field) : string
    {
        $pivotTable = $this->relationship->getTable();
        $fromAlias = $this->option('from_table_alias');
        $toAlias = $this->option('to_table_alias');

        $context = collect([$pivotTable, $fromAlias, $toAlias])->filter()->implode('_');

        return static::getQualifiedField($field, $pivotTable, $context);
    }

    /**
     * @param Relation $relationship
     * @return bool
     */
    public static function instanceOf(Relation $relationship): bool
    {
        return $relationship instanceof BelongsToMany;
    }
}
