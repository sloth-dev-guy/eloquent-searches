<?php

namespace SlothDevGuy\Searches\Join;

use SlothDevGuy\Searches\BuilderOptions;
use SlothDevGuy\Searches\JoinRelationshipBuilder;
use SlothDevGuy\Searches\SearchBuilder;
use SlothDevGuy\Searches\Searcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

/**
 * Class SearchJoinRelationshipBuilder
 * @package SlothDevGuy\Searches\Join
 */
class SearchJoinRelationshipBuilder implements SearchBuilder
{
    use BuilderOptions;

    /**
     * @var string[]
     */
    protected static $supportedJoins = [
        'join' => 'join',
        'inner-join' => 'join',
        'left-join' => 'leftJoin',
        'right-join' => 'rightJoin',
        'cross-join' => 'crossJoin',
    ];

    /**
     * @var array|JoinRelationshipBuilder[]
     */
    protected static $supportedRelationships = [
        'belongs-to' => JoinBelongsTo::class,
        'has-one' => JoinHasOneOrMany::class,
        'has-many' => JoinHasOneOrMany::class,
        'has-one-or-many' => JoinHasOneOrMany::class,
        'morph-one-or-many' => JoinMorphOneOrMany::class,
        'morph-one' => JoinMorphOneOrMany::class,
        'morph-many' => JoinMorphOneOrMany::class,
        'morph-to-many' => JoinMorphToMany::class,
    ];

    /**
     * @var Collection
     */
    protected Collection $arguments;

    /**
     * @var JoinRelationshipBuilder|null
     */
    protected ?JoinRelationshipBuilder $join = null;

    /**
     * @param Searcher $search
     * @param string $relationshipWithAggregations
     * @param mixed $nestedConditions
     * @param array $options
     */
    protected function __construct(
        protected Searcher $search,
        protected string   $relationshipWithAggregations,
        protected          $nestedConditions,
        array              $options = [],
    )
    {
        $this->options = static::defaultOptions(array_merge([
            'from_alias' => $this->search()->option('from_alias'),
        ], $options));

        $this->buildJoinArguments();
    }

    /**
     * @return Searcher
     */
    public function search() : Searcher
    {
        return $this->search;
    }

    /**
     * @return JoinRelationshipBuilder|null
     */
    public function join() : JoinRelationshipBuilder|null
    {
        return $this->join;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function pushInQueryBuilder(Builder $builder): Builder
    {
        if(!$this->join()) {
            //class, method, reason, action
            $this->logError(__FUNCTION__, 'unsupported-join', 'skip-join', [
                'raw_relationship' => $this->relationshipWithAggregations,
                'from_model' => get_class($this->search()->from()),
            ]);

            return $builder;
        }

        $builder = $this->join()->pushInBuilder($builder);

        //recursive call (the nest conditions will be handled by another searcher instance)
        $search = $this->search();
        $search = new $search($this->join()->to(), $this->nestedConditions, $builder, [
            'from_alias' => $this->join()->option('to_table_alias'),
        ]);

        return $search->builder();
    }

    /**
     * @return $this
     */
    protected function buildJoinArguments()
    {
        $arguments = collect(explode('|', $this->relationshipWithAggregations))
            ->map('trim');

        //the relation should be the one in curly brackets or by default the first one
        $matches = [];
        $relation = $arguments->first(function ($argument) use (&$matches){
            return preg_match('/^\{(.*)}$/', $argument, $matches);
        });

        if ($relation) {
            $relation = array_pop($matches);
        }

        $relation = $relation ? : $arguments->shift();

        @list($relation, $alias) = explode('@', $relation);

        $this->options['relation'] = $relation;
        $this->options['alias'] = $alias;

        $this->arguments = $arguments->map(function ($argument){
            if (in_array($argument, static::$supportedJoins)) {
                $this->options['method'] = $argument;
            } elseif (isset(static::$supportedJoins[$argument])) {
                $this->options['method'] = static::$supportedJoins[$argument];
            }

            return $argument;
        });

        try{
            $from = $this->search()->from();

            if (!method_exists($from, $relation))
                return $this;

            $relationship = call_user_func_array([$from, $relation], []);

            if(!($relationship instanceof Relation)){
                $this->logDebug(__FUNCTION__, 'relationship-call-error', 'leave-empty-join-arguments');

                return $this;
            }

            $relationshipKey = Str::kebab(class_basename($relationship));
            $this->options['relationship'] = $relationshipKey;

            $joinAdapter = static::$supportedRelationships[$relationshipKey];
            if($joinAdapter::instanceOf($relationship)){
                $this->join = new $joinAdapter($from, $relationship, [
                    'method' => $this->options['method'],
                    'from_table_alias' => $this->search()->option('from_alias'),
                    'to_table_alias' => $alias? : null,
                ]);
            }

            if(!$this->join){
                $this->logDebug(__FUNCTION__, 'unsupported-relationship-call-error', 'null-join');
            }
        }
        catch (Exception $ex){
            $this->logError(__FUNCTION__, 'relationship-call-error', 'leave-empty-join-arguments', [
                'exception' => get_class($ex),
                'exception_message' => $ex->getMessage(),
            ]);

            return $this;
        }

        return $this;
    }

    /**
     * @param string $method
     * @param string $reason
     * @param string $action
     * @param array $context
     * @return $this
     */
    protected function logDebug(string $method, string $reason, string $action, array $context = [])
    {
        $class = class_basename($this);

        $context = array_merge($this->options, $context);

        Log::debug(implode(', ', compact('class', 'method', 'reason', 'action')), $context);

        return $this;
    }

    /**
     * @param string $method
     * @param string $reason
     * @param string $action
     * @param array $context
     * @return $this
     */
    protected function logError(string $method, string $reason, string $action, array $context = [])
    {
        $class = class_basename($this);

        $context = array_merge($this->options, $context);

        Log::error(implode(', ', compact('class', 'method', 'reason', 'action')), $context);

        return $this;
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

        if(!$builder->join()){
            $builder = null;
        }

        return $builder;
    }

    /**
     * @param array $options
     * @return array
     */
    public static function defaultOptions(array $options = []): array
    {
        return array_merge([
            'relation' => null,
            'method' => 'join',
        ], $options);
    }
}
