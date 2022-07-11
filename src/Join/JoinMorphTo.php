<?php

namespace SlothDevGuy\Searches\Join;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class JoinMorphTo
 * @package SlothDevGuy\Searches\Join
 */
class JoinMorphTo extends JoinRelationship
{
    /**
     * @param Model $from
     * @param MorphTo $relationship
     * @param array $options
     */
    public function __construct(
        protected Model $from,
        protected  MorphTo $relationship,
        array $options = []
    )
    {
        $this->options = static::defaultOptions($options);
    }

    public function to(): Model
    {
        return $this->option('to')? : parent::to();
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
     * @return array
     */
    public function on() : array
    {
        $relationship = $this->relationship;

        $first = $this->getToTableQualifiedField($relationship->getOwnerKeyName()? : $this->to()->getKeyName());
        $operator = $this->joinOperator();
        $second = $this->getFromTableQualifiedField($relationship->getForeignKeyName());

        return compact('first', 'operator', 'second');
    }

    /**
     * @return array
     */
    public function wheres() : array
    {
        $relationship = $this->relationship;

        $first = $this->getFromTableQualifiedField($relationship->getMorphType());
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
        return get_class($this->to());
    }

    /**
     * @param Relation $relationship
     * @return bool
     */
    public static function instanceOf(Relation $relationship): bool
    {
        return $relationship instanceof MorphTo;
    }

    /**
     * @param array $options
     * @return array
     */
    protected static function defaultOptions(array $options = []): array
    {
        $options = parent::defaultOptions($options);

        if($to = collect(data_get($options, 'relation_arguments'))->first()){
            //classmap is a needed here
            data_set($options, 'to', new $to);
        }

        return $options;
    }
}
