<?php

namespace SlothDevGuy\Searches\Where;

use SlothDevGuy\Searches\BuilderOptions;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class FieldUnPacker
 * @package SlothDevGuy\Searches\Where
 */
class WhereArguments implements Arrayable
{
    use BuilderOptions;

    /**
     * @todo Handle by a config file? inside the search where builder
     *
     * @var array
     */
    protected static array $argumentsList = [
        'not' => ['!', 'not'],
        'string_search' => ['_%', '%_', '%%', '%_%', '_'],
        'operators' => ['=', '<>', '>', '<', '>=', '<='],
        'methodPattern' => '/^where([a-z\-]*)$/',
        'method_aliases' => [
            'whereNull' => ['null'],
            'whereBetween' => ['><', 'between'],
            'whereColumn' => ['column'],
        ],
    ];

    /**
     * All the aggregations provided in the raw field
     *
     * @var Collection
     */
    protected Collection $arguments;

    /**
     * @param string $fieldWithArguments
     * @param mixed $value
     * @param array $options
     */
    public function __construct(
        protected string $fieldWithArguments,
        protected $value,
        array  $options = []
    )
    {
        $this->options = static::defaultOptions($options);

        $this->unpackArguments();
    }

    /**
     * @param string|null $method
     * @return string
     */
    public function method(string $method = null) : string
    {
        $this->setOptionIfNotNull('method', $method);

        return $this->options['method'];
    }

    /**
     * @param string|null $field
     * @return string|array
     */
    public function field(string $field = null) : string|array
    {
        $this->setOptionIfNotNull('field', $field);

        $fields = collect(explode(',', $this->options['field']));

        return $fields->count() == 1? $fields->first() : $fields->toArray();
    }

    /**
     * @param string|null $operator
     * @return string
     */
    public function operator(string $operator = null) : string
    {
        $this->setOptionIfNotNull('operator', $operator);

        return $this->options['operator'];
    }

    /**
     * @return mixed
     */
    public function value()
    {
        if(func_num_args() > 0){
            $this->value = func_get_args()[0];
        }

        return $this->value;
    }

    /**
     * @param string|null $operand
     * @return string
     */
    public function operand(string $operand = null) : string
    {
        $this->setOptionIfNotNull('operand', $operand);

        return $this->options['operand'];
    }

    /**
     * @param bool|null $not
     * @return bool
     */
    public function not(bool $not = null) : bool
    {
        $this->setOptionIfNotNull('not', $not);

        return $this->options['not'];
    }


    /**
     * @param string $key
     * @param mixed $value
     * @return static
     */
    protected function setOptionIfNotNull(string $key, $value) : static
    {
        if(!is_null($value))
            data_set($this->options, $key, $value);

        return $this;
    }

    /**
     * @return $this
     */
    public function unpackArguments() : static
    {
        $fieldWithArguments = $this->fieldWithArguments;

        $arguments = collect(explode('|', $fieldWithArguments))->map('trim');

        //the field should be the one in curly brackets or by default the first one
        $matches = [];
        $field = $arguments->first(function ($argument) use(&$matches){
            return preg_match('/^\{(.*)}$/', $argument, $matches);
        });

        if($field){
            $field = array_pop($matches);
        }
        $this->options['field'] = (string) ($field? : $arguments->shift());

        /** arguments evaluations */

        $this->arguments = $arguments->map(function ($argument){
            //not evaluation (plain and simple)
            if(in_array($argument, static::$argumentsList['not'])){
                $this->options['not'] = true;
            }
            //operator evaluation
            elseif (in_array($argument, static::$argumentsList['operators'])){
                $this->options['operator'] = $argument;
            }
            //string search with like support
            elseif(in_array($argument, static::$argumentsList['string_search'])){
                $this->options['operator'] = 'like';
                $argument === '%%' && ($argument = '%_%');

                $this->options['wildcard'] = $argument;
            }
            //where method
            elseif (preg_match(static::$argumentsList['methodPattern'], $argument)){
                $this->options['method'] = Str::camel($argument);
            }
            elseif ($argument === 'or'){
                $this->options['operand'] = 'or';
            }
            //where method aliases
            elseif ($method = $this->getMethodFromAliases($argument)){
                $this->options['method'] = $method;
            }
            //by default a custom option as option:arg_1,arg_2
            else{
                $parts = explode(':', $argument);

                if(count($parts) === 1){
                    //no arguments then only a flag with true value
                    $this->options[$argument] = true;
                }
                elseif (count($parts) > 1){
                    $key = array_shift($parts);
                    $arguments = array_map('trim', explode(',', array_pop($parts)));

                    $this->options[$key] = $arguments;
                }
            }

            return $argument;
        });

        return $this;
    }

    /**
     * @param string $argument
     * @return string|null
     */
    protected function getMethodFromAliases(string $argument) : string|null
    {
        foreach (static::$argumentsList['method_aliases'] as $method => $aliases){
            if(in_array($argument, $aliases)){
                return $method;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->options, [
            'value' => $this->value(),
            'field_with_arguments' => $this->fieldWithArguments,
        ]);
    }

    /**
     * @param array $options
     * @return array
     */
    public static function defaultOptions(array $options = []): array
    {
        return array_merge([
            'method' => 'where',
            'field' => null,
            'operator' => '=',
            'operand' => 'and',
            'not' => false,
        ], $options);
    }
}
