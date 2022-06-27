<?php

namespace Tests\Where;

use SlothDevGuy\Searches\Where\SearchWhereBuilder;
use SlothDevGuy\Searches\Where\WhereIn;
use Tests\TestCase;
use Tests\WithDB;

/**
 * Class WhereInTest
 * @package Tests\Where
 */
class WhereInTest extends TestCase
{
    use WithDB, MockSearcher, AssertWhereBuilderDefaults;

    public function testIn()
    {
        $field = 'foo';
        $values = ['bar', 'some', 'data'];

        $aliases = [null, 'in', 'where-in'];

        foreach ($aliases as $alias){
            $key = implode('|', array_filter([$field, $alias]));

            /** @var SearchWhereBuilder $whereBuilder */
            $whereBuilder = SearchWhereBuilder::buildFromKeyAndValue($this->mockSearcher(), $key, $values);

            $this->assertNotNull($whereBuilder);

            $this->assertWhereBuilder($whereBuilder, $field, $values);
        }
    }

    public function testNotIn()
    {
        $field = 'foo';
        $values = ['bar', 'some', 'data'];

        $aliases = [null, 'in', 'where-in'];

        $negations = collect(['not', '!']);

        foreach ($aliases as $alias){
            $not = $negations->random();
            $key = implode('|', array_filter([$field, $not, $alias]));

            /** @var SearchWhereBuilder $whereBuilder */
            $whereBuilder = SearchWhereBuilder::buildFromKeyAndValue($this->mockSearcher(), $key, $values);

            $this->assertNotNull($whereBuilder);

            $this->assertWhereBuilder($whereBuilder, $field, $values, [
                'method' => 'whereNotIn'
            ]);
        }
    }

    /**
     * @return array
     */
    protected function whereArguments()
    {
        return [
            'method' => 'whereIn',
            'where_instance_of' => WhereIn::class,
        ];
    }
}
