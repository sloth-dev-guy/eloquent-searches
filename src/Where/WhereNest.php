<?php

namespace SlothDevGuy\Searches\Where;

/**
 * Class WhereNest
 * @package SlothDevGuy\Searches\Where
 */
class WhereNest extends BaseWhere
{
    /**
     * @var array|string[]
     */
    protected static array $supportedMethods = ['where', 'orWhere'];

    /**
     * @var array|string[]
     */
    protected static array $negatedMethods = [
        'where' => 'whereNot',
        'orWhere' => 'orWhereNot',
    ];

    /**
     * @var null
     */
    protected $negatedMethod = null;

    /**
     * @return static
     */
    protected function negateIfRequired()
    {
        $arguments = $this->arguments();

        if($arguments->not()) {
            $this->negatedMethod = static::$negatedMethods[$arguments->method()];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function where(): array
    {
        $method = $this->arguments()->method();

        $this->negateIfRequired();

        $nestCallback = function ($query){
            $values = $this->arguments()->value();

            $searcher = $this->searchBuilder()->searcher();

            $searcher = new $searcher($searcher->from(), $values, $query, $searcher->options());

            return $searcher->builder();
        };

        //if a negated method is present a closure must be offered
        if($this->negatedMethod){
            $negatedNestCallback = function ($query) use($method, $nestCallback) {
                $query->{$method}($nestCallback);

                return $query;
            };

            return [
                'method' => $this->negatedMethod,
                'arguments' => [$negatedNestCallback],
            ];
        }

        //pack where method and arguments
        return [
            'method' => $method,
            'arguments' => [$nestCallback],
        ];
    }
}
