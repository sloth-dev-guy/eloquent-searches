<?php

namespace Tests\Where;

use SlothDevGuy\Searches\Where\SearchWhereBuilder;
use SlothDevGuy\Searches\Where\WhereColumn;
use Tests\TestCase;

class WhereColumnTest extends TestCase
{
    use MockSearcher, WhereBuilderAssertions;

    public function testWhere()
    {
        $field = 'foo';
        $value = 'bar';

        $aliases = ['column', 'where-column'];

        foreach ($aliases as $alias){
            $key = implode('|', array_filter([$field, $alias]));

            /** @var SearchWhereBuilder $whereBuilder */
            $whereBuilder = SearchWhereBuilder::buildFromKeyAndValue($this->mockSearcher(), $key, $value);

            $this->assertNotNull($whereBuilder);

            $this->assertWhereBuilder($whereBuilder, [
                'where_instance_of' => WhereColumn::class,
                'method' => 'whereColumn',
                'arguments' => [
                    "test.{$field}",
                    '=',
                    $value,
                ]
            ]);
        }
    }

    /**
     * @return void
     */
    public function testOperators()
    {
        $operators = ['=', '<>', '>', '>=', '<', '<='];
        $field = 'foo';
        $value = 'bar';
        $aliases = collect(['column', 'where-column']);

        foreach ($operators as $operator){
            $key = "$field|{$aliases->random()}|$operator";

            /** @var SearchWhereBuilder $whereBuilder */
            $whereBuilder = SearchWhereBuilder::buildFromKeyAndValue($this->mockSearcher(), $key, $value);

            $this->assertNotNull($whereBuilder);

            $this->assertWhereBuilder($whereBuilder, [
                'where_instance_of' => WhereColumn::class,
                'method' => 'whereColumn',
                'arguments' => [
                    "test.{$field}",
                    $operator,
                    $value
                ]
            ]);
        }
    }

    /**
     * @return void
     */
    public function testNegation()
    {
        $negatedOperators = [
            '=' => '<>',
            '<>' => '=',
            '>' => '<=',
            '>=' => '<',
            '<' => '>=',
            '<=' => '>',
        ];

        $field = 'foo';
        $value = 'bar';
        $not = collect(['not', '!']);
        $aliases = collect(['column', 'where-column']);

        foreach ($negatedOperators as $operator => $negatedOperator){
            $key = "$field|{$aliases->random()}|{$not->random()}|$operator";

            /** @var SearchWhereBuilder $whereBuilder */
            $whereBuilder = SearchWhereBuilder::buildFromKeyAndValue($this->mockSearcher(), $key, $value);

            $this->assertNotNull($whereBuilder);

            $this->assertWhereBuilder($whereBuilder, [
                'where_instance_of' => WhereColumn::class,
                'method' => 'whereColumn',
                'arguments' => [
                    "test.{$field}",
                    $negatedOperator,
                    $value
                ]
            ]);
        }
    }
}
