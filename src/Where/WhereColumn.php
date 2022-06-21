<?php

namespace SlothDevGuy\Searches\Where;

/**
 * Class WhereColumn
 * @package SlothDevGuy\Searches\Where
 */
class WhereColumn extends Where
{
    /**
     * @var array
     */
    protected static array $supportedMethods = ['whereColumn', 'orWhereColumn'];

    /**
     * @return BaseWhere
     */
    public function redirect(): BaseWhere
    {
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

        //pack where method and arguments
        return [
            'method' => $arguments->method(),
            'arguments' => compact('field', 'operator', 'value'),
        ];
    }
}
