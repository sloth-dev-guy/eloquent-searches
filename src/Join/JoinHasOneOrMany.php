<?php

namespace SlothDevGuy\Searches\Join;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class HasOneOrManyAdapter
 * @package SlothDevGuy\Searches\Join
 */
class JoinHasOneOrMany extends JoinRelationship
{
    /**
     * @param Model $from
     * @param HasOneOrMany $relationship
     * @param array $options
     */
    public function __construct(
        protected  Model     $from,
        protected  HasOneOrMany $relationship,
        array      $options = [],
    )
    {
        $this->options = static::defaultOptions($options);
    }

    /**
     * @return array[]
     */
    public function joins() : array
    {
        $relationship = $this->relationship;

        $first = $this->getFromTableQualifiedField($relationship->getLocalKeyName());
        $operator = $this->joinOperator();
        $second = $this->getToTableQualifiedField($relationship->getForeignKeyName());

        $baseJoin = [
            'table' => $this->getJoinContext(),
            'arguments' => [$first, $operator, $second],
        ];

        return [$baseJoin];
    }

    /**
     * @param Relation $relationship
     * @return bool
     */
    public static function instanceOf(Relation $relationship) : bool
    {
        return $relationship instanceof HasOneOrMany;
    }
}
