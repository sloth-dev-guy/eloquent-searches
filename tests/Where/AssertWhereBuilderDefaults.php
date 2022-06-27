<?php

namespace Tests\Where;

use SlothDevGuy\Searches\Where\SearchWhereBuilder;

/**
 * Trait AssertWhereBuilderDefaults
 * @package Tests\Where
 */
trait AssertWhereBuilderDefaults
{
    /**
     * @param SearchWhereBuilder $whereBuilder
     * @param string $field
     * @param array $values
     * @param array $expected
     * @return void
     */
    protected function assertWhereBuilder(SearchWhereBuilder $whereBuilder, string $field, array $values, array $expected = [])
    {
        $whereArguments = $this->whereArguments();

        $expected = array_merge([
            'method' => data_get($whereArguments, 'method'),
        ], $expected);

        $where = $whereBuilder->where();

        $this->assertInstanceOf(data_get($whereArguments, 'where_instance_of'), $where);

        $wherePayload = $where->where();

        $this->assertEquals(data_get($expected, 'method'), data_get($wherePayload, 'method'));

        $arguments = [
            "test.{$field}",
            $values
        ];

        $whereArguments = array_values(data_get($wherePayload, 'arguments', []));

        $this->assertSameSize($arguments, $whereArguments);

        foreach ($arguments as $index => $value)
            $this->assertEquals($value, $whereArguments[$index]);
    }

    /**
     * @return null[]
     */
    protected function whereArguments()
    {
        return [
            'method' => null,
            'where_instance_of' => null,
        ];
    }
}
