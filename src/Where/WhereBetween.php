<?php

namespace SlothDevGuy\Searches\Where;

/**
 * Class WhereBetween
 * @package SlothDevGuy\Searches\Where
 */
class WhereBetween extends BaseWhere
{
    use NegatedWhereMethods;

    /**
     * @var array|string[]
     */
    protected static array $supportedMethods = [
        'whereBetween',
        'whereNotBetween',
        'orWhereBetween',
        'orWhereNotBetween',
        'whereBetweenColumns',
        'whereNotBetweenColumns',
        'orWhereBetweenColumns',
        'orWhereNotBetweenColumns',
    ];

    /**
     * @var array|string[]
     */
    protected static array $negatedMethods = [
        'whereBetween' => 'whereNotBetween',
        'whereNotBetween' => 'whereBetween',
        'whereBetweenColumns' => 'whereNotBetweenColumns',
        'whereNotBetweenColumns' => 'whereBetweenColumns',
        'orWhereBetween' => 'orWhereNotBetween',
        'orWhereNotBetween' => 'orWhereBetween',
        'orWhereBetweenColumns' => 'orWhereNotBetweenColumns',
        'orWhereNotBetweenColumns' => 'orWhereBetweenColumns',
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
        if($response = $this->isValidArray($value)){
            return $response;
        }

        $values = collect($value)
            ->sort()
            ->values()
            ->toArray();

        //pack where method and arguments
        return [
            'method' => $arguments->method(),
            'arguments' => compact('field', 'values'),
        ];
    }

    /**
     * @param mixed $value
     * @return array|false
     */
    protected function isValidArray($value)
    {
        //check value type
        if(!is_array($value)){
            return [
                'method' => null,
                'reason' => 'value-not-an-array',
                'action' => 'skip-where',
            ];
        }

        if(count($value) !== 2){
            return [
                'method' => null,
                'reason' => 'value-must-have-two-elements',
                'action' => 'skip-where',
            ];
        }

        return false;
    }
}
