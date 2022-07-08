<?php

namespace SlothDevGuy\Searches\Join;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;

class JoinMorphOneOrMany extends JoinRelationship
{
    /**
     * @param Model $from
     * @param MorphOneOrMany $relationship
     * @param array $options
     */
    public function __construct(
        protected Model $from,
        protected MorphOneOrMany $relationship,
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
        $baseJoin = [
            'table' => $this->getJoinContext(),
            'arguments' => [$this->joinClosure()],
        ];

        return [$baseJoin];
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

        $first = $this->getFromTableQualifiedField($relationship->getLocalKeyName());
        $operator = $this->joinOperator();
        $second = $this->getToTableQualifiedField($relationship->getForeignKeyName());

        return compact('first', 'operator', 'second');
    }

    /**
     * @return array
     */
    public function wheres() : array
    {
        $relationship = $this->relationship;

        $first = $this->getToTableQualifiedField($relationship->getMorphType());
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

    /**
     * @param Relation $relationship
     * @return bool
     */
    public static function instanceOf(Relation $relationship) : bool
    {
        return $relationship instanceof MorphOneOrMany;
    }
}
