<?php

namespace Tests\Where;

use SlothDevGuy\Searches\Where\SearchWhereBuilder;

/**
 * Trait AssertWhereBuilderDefaults
 * @package Tests\Where
 */
trait WhereBuilderAssertions
{
    /**
     * @param SearchWhereBuilder $whereBuilder
     * @param array $expected
     * @return void
     */
    protected function assertWhereBuilder(SearchWhereBuilder $whereBuilder, array $expected)
    {
        $where = $whereBuilder->where();

        $this->assertInstanceOf(data_get($expected, 'where_instance_of'), $where);

        $wherePayload = $where->where();

        $this->assertEquals(data_get($expected, 'method'), data_get($wherePayload, 'method'));

        $expectedArguments = data_get($expected, 'arguments', []);

        $whereArguments = array_values(data_get($wherePayload, 'arguments', []));

        $this->assertSameSize($expectedArguments, $whereArguments);

        foreach ($expectedArguments as $index => $value)
            $this->assertEquals($value, $whereArguments[$index]);
    }
}
