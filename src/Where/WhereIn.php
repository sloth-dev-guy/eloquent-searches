<?php

namespace SlothDevGuy\Searches\Where;

/**
 * Class WhereIn
 * @package SlothDevGuy\Searches\Where
 */
class WhereIn extends BaseWhere
{
    use NegatedWhereMethods;

    /**
     * @var array|string[]
     */
    protected static array $supportedMethods = ['whereIn', 'whereNotIn', 'orWhereIn', 'orWhereNotIn'];

    /**
     * @var array|string[]
     */
    protected static array $negatedMethods = [
        'whereIn' => 'whereNotIn',
        'whereNotIn' => 'whereIn',
        'orWhereIn' => 'orWhereNotIn',
        'orWhereNotIn' => 'orWhereIn',
    ];

    /**
     * @return BaseWhere
     */
    public function redirect(): BaseWhere
    {
        $arguments = $this->arguments();

        $value = $arguments->value();

        //check if we need to redirect the method
        if(!is_array($value)){
            $arguments->method('where');

            return new WhereIn($this->searchBuilder());
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

        $field = $this->getQualifiedField($arguments->field());
        $value = (array) $arguments->value();

        empty($value) && warning('where-in with empty values', [
            'field' => $field,
        ]);

        //pack where method and arguments
        return [
            'method' => $arguments->method(),
            'arguments' => compact('field', 'value'),
        ];
    }
}
