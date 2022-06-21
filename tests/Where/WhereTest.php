<?php

namespace Tests\Where;

use SlothDevGuy\Searches\Searcher;
use SlothDevGuy\Searches\Where\SearchWhereBuilder;
use SlothDevGuy\Searches\Where\Where;
use Tests\TestCase;
use Tests\WithDB;

/**
 * Class WhereTest
 * @package Tests\Where
 */
class WhereTest extends TestCase
{
    use WithDB;

    /**
     * @return void
     */
    public function testEquals()
    {
        $key = 'foo';
        $value = 'bar';

        /** @var SearchWhereBuilder $whereBuilder */
        $whereBuilder = SearchWhereBuilder::buildFromKeyAndValue($this->mockSearcher(), $key, $value);

        $this->assertNotNull($whereBuilder);

        $this->assertWhereBuilder($whereBuilder, $key, '=', $value);
    }

    /**
     * @return void
     */
    public function testOperators()
    {
        $operators = ['=', '<>', '>', '>=', '<', '<=', '%_%', '_%', '%_'];
        $field = 'foo';
        $value = 'bar';

        foreach ($operators as $operator){
            $key = "$field|$operator";

            /** @var SearchWhereBuilder $whereBuilder */
            $whereBuilder = SearchWhereBuilder::buildFromKeyAndValue($this->mockSearcher(), $key, $value);

            $this->assertNotNull($whereBuilder);

            if(in_array($operator, ['%_%', '_%', '%_'])){
                $value = str_replace('_', $value, $operator);
                $operator = 'like';
            }

            $this->assertWhereBuilder($whereBuilder, $field, $operator, $value);
        }
    }

    /**
     * @param SearchWhereBuilder $whereBuilder
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return void
     */
    protected function assertWhereBuilder(SearchWhereBuilder $whereBuilder, $field, $operator, $value)
    {
        $where = $whereBuilder->where();

        $this->assertInstanceOf(Where::class, $where);

        $wherePayload = $where->where();

        $this->assertEquals('where', data_get($wherePayload, 'method'));

        $arguments = [
            "test.{$field}",
            $operator,
            $value
        ];

        $whereArguments = array_values(data_get($wherePayload, 'arguments', []));

        $this->assertSameSize($arguments, $whereArguments);

        foreach ($arguments as $index => $value)
            $this->assertEquals($value, $whereArguments[$index]);
    }

    /**
     * @return Searcher
     */
    protected function mockSearcher() : Searcher
    {
        $searcher = $this->createMock(Searcher::class);

        $searcher->expects($this->atLeastOnce())
            ->method('getFromQualifiedField')
            ->will($this->returnCallback(fn($field) => "test.{$field}"));

        return $searcher;
    }
}
