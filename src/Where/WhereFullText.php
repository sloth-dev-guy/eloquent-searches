<?php

namespace SlothDevGuy\Searches\Where;

/**
 * Class WhereFullText
 * @package SlothDevGuy\Searches\Where
 */
class WhereFullText extends BaseWhere
{
    /**
     * @var array|string[]
     */
    protected static array $supportedMethods = ['whereFullText', 'orWhereFullText'];

    /**
     * @var array|string[]
     */
    protected static array $negatedMethods = [
        'whereFullText' => 'whereNot',
        'orWhereFullText' => 'orWhereNot',
    ];

    /**
     * @var null
     */
    protected $negatedMethod = null;

    /**
     * @return $this|WhereFullText
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
        $arguments = $this->arguments();

        //check if we need to change the operator
        $this->negateIfRequired();

        //or the method for the operand
        $this->syncMethodWithOperand();

        //unpack field, operator and value
        $columns = collect($arguments->field())
            ->map(fn($field) => $this->getQualifiedField($field))
            ->toArray();
        $value = $arguments->value();

        //check value type
        if($response = $this->isValeInvalid($value)){
            return $response;
        }

        $method = $arguments->method();
        $arguments = compact('columns', 'value');

        //if a negated method is present a closure must be offered
        if($this->negatedMethod){
            $callback = function ($query) use($method, $arguments) {
                $arguments = array_values($arguments);

                $query->{$method}(...$arguments);
            };

            return [
                'method' => $this->negatedMethod,
                'arguments' => [$callback],
            ];
        }

        //pack where method and arguments
        return [
            'method' => $method,
            'arguments' => compact('columns', 'value'),
        ];
    }

    /**
     * @param mixed $value
     * @return array|false
     */
    protected function isValeInvalid(mixed $value)
    {
        if(is_array($value)){
            return [
                'method' => null,
                'reason' => 'array-value',
                'action' => 'skip-where',
            ];
        }

        if(strlen($value) <= 3){
            return [
                'method' => null,
                'reason' => 'value-must-have-at-least-3-characters',
                'action' => 'skip-where',
            ];
        }

        return false;
    }
}
