<?php

namespace SlothDevGuy\Searches\Where;

/**
 * Class WhereNull
 * @package SlothDevGuy\Searches\Where
 */
class WhereNull extends BaseWhere
{
    use NegatedWhereMethods;

    /**
     * @var array|string[]
     */
    protected static array $supportedMethods = ['whereNull', 'whereNotNull', 'orWhereNull', 'orWhereNotNull'];

    /**
     * @var array|string[]
     */
    protected static array $negatedMethods = [
        'whereNull' => 'whereNotNull',
        'whereNotNull' => 'whereNull',
        'orWhereNull' => 'orWhereNotNull',
        'orWhereNotNull' => 'orWhereNull',
    ];

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

        $field = $this->getQualifiedField($arguments->field());
        $value = $arguments->value();

        //check value type
        if($response = $this->emptyValue($value)){
            return $response;
        }

        //pack where method and arguments
        return [
            'method' => $arguments->method(),
            'arguments' => compact('field'),
        ];
    }

    /**
     * @param mixed $value
     * @return array|false
     */
    protected function emptyValue(mixed $value)
    {
        if(!$value){
            return [
                'method' => null,
                'reason' => 'empty-value',
                'action' => 'skip-where',
            ];
        }

        return false;
    }
}
