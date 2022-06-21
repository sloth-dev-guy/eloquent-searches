<?php

namespace SlothDevGuy\Searches\Where;

/**
 * Class Where
 * @package SlothDevGuy\Searches\Where
 */
class Where extends BaseWhere
{
    /**
     * @var array
     */
    protected static array $supportedMethods = ['where', 'orWhere'];

    /**
     * @var array|string[]
     */
    protected static array $negatedOperators = [
        '=' => '<>',
        '<>' => '=',
        '>' => '<=',
        '>=' => '<',
        '<' => '>=',
        '<=' => '>',
        'like' => 'not like',
        'not like' => 'like',
    ];

    /**
     * @return BaseWhere
     */
    public function redirect() : BaseWhere
    {
        $arguments = $this->arguments();

        //check if is a nest Where
        if($arguments->option('nest')){
            return new WhereNest($this->searchBuilder());
        }

        $value = $arguments->value();

        //check if we need to redirect the method
        if(is_array($value)){
            $arguments->method('whereIn');

            return new WhereIn($this->searchBuilder());
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function negateIfRequired()
    {
        $arguments = $this->arguments();

        if($arguments->not()) {
            $arguments->operator(static::$negatedOperators[$arguments->operator()]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function where() : array
    {
        $arguments = $this->arguments();

        //check if we need to change the operator
        $this->negateIfRequired();

        //or the method for the operand
        $this->syncMethodWithOperand();

        //unpack field, operator and value
        $field = $this->getQualifiedField($arguments->field());
        $operator = $arguments->operator();
        $value = $arguments->value();

        //check value type
        if($response = $this->isValueAnArray($value)){
            return $response;
        }

        //is string(like) search and has a wildcard set
        if($operator === 'like' && ($wildcard = $arguments->option('wildcard'))){
            $value = str_replace('_', $value, $wildcard);
        }

        //pack where method and arguments
        return [
            'method' => $arguments->method(),
            'arguments' => compact('field', 'operator', 'value'),
        ];
    }

    /**
     * @param mixed $value
     * @return array|false
     */
    protected function isValueAnArray($value)
    {
        //check value type
        if(is_array($value)){
            return [
                'method' => null,
                'reason' => 'array-value',
                'action' => 'skip-where',
            ];
        }

        return false;
    }
}
