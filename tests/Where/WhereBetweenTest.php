<?php

namespace Tests\Where;

use SlothDevGuy\Searches\Where\SearchWhereBuilder;
use SlothDevGuy\Searches\Where\WhereBetween;
use Tests\TestCase;
use Tests\WithDB;

/**
 * Class WhereBetween
 * @package Tests\Where
 */
class WhereBetweenTest extends TestCase
{
    use MockSearcher, WhereBuilderAssertions;

    public function testBetween()
    {
        $field = 'foo';
        $values = ['range_1', 'range_2'];

        $aliases = ['between', 'where-between', '><'];

        foreach ($aliases as $alias){
            $key = implode('|', array_filter([$field, $alias]));

            /** @var SearchWhereBuilder $whereBuilder */
            $whereBuilder = SearchWhereBuilder::buildFromKeyAndValue($this->mockSearcher(), $key, $values);

            $this->assertNotNull($whereBuilder);

            $this->assertWhereBuilder($whereBuilder, [
                'where_instance_of' => WhereBetween::class,
                'method' => 'whereBetween',
                'arguments' => [
                    "test.{$field}",
                    $values,
                ]
            ]);
        }
    }

    public function testNotBetween()
    {
        $field = 'foo';
        $values = ['range_1', 'range_2'];

        $aliases = ['between', 'where-between', '><'];
        $negations = collect(['not', '!']);

        foreach ($aliases as $alias){
            $not = $negations->random();
            $key = implode('|', array_filter([$field, $not, $alias]));

            /** @var SearchWhereBuilder $whereBuilder */
            $whereBuilder = SearchWhereBuilder::buildFromKeyAndValue($this->mockSearcher(), $key, $values);

            $this->assertNotNull($whereBuilder);

            $this->assertWhereBuilder($whereBuilder, [
                'where_instance_of' => WhereBetween::class,
                'method' => 'whereNotBetween',
                'arguments' => [
                    "test.{$field}",
                    $values,
                ]
            ]);
        }
    }
}
