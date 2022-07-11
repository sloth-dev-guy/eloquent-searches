<?php

namespace SlothDevGuy\Searches\Join;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class BelongsToAdapter
 * @package SlothDevGuy\Searches\Join
 */
class JoinBelongsTo extends JoinRelationship
{
    /**
     * @param Model $from
     * @param BelongsTo $relationship
     * @param array $options
     */
    public function __construct(
        protected  Model     $from,
        protected  BelongsTo $relationship,
        array      $options = [],
    )
    {
        $this->options = static::defaultOptions($options);
    }

    public function on(): array
    {
        $relationship = $this->relationship;

        $first = $this->getToTableQualifiedField($relationship->getOwnerKeyName());
        $operator = $this->joinOperator();
        $second = $this->getFromTableQualifiedField($relationship->getForeignKeyName());

        return compact('first', 'operator', 'second');
    }

    /**
     * @param Relation $relationship
     * @return bool
     */
    public static function instanceOf(Relation $relationship) : bool
    {
        return $relationship instanceof BelongsTo;
    }
}
