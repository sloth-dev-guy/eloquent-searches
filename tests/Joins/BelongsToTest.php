<?php

namespace Tests\Joins;

use SlothDevGuy\Searches\Join\JoinBelongsTo;
use SlothDevGuy\Searches\Join\SearchJoinRelationshipBuilder;
use Tests\database\Address;
use Tests\database\Country;
use Tests\database\Location;
use Tests\TestCase;

/**
 * Class BelongsToTest
 * @package Tests\Joins
 */
class BelongsToTest extends TestCase
{
    use MockSearcher, JoinBuilderAssertions;

    public function testJoins()
    {
        $this->loadTestModels();

        $joinData = [
            'values' => [],
            'model' => Location::class,
            'table' => 'location',
            'relation' => 'location',
            'foreign_key' => 'location_id',
            'aliases' => [null, 'foo', 'bar']
        ];

        $joinTypes = [null, 'join', 'left-join'];

        $from = [
            'model' => $model = new Address(),
            'table' => $model->getTable(),
        ];

        foreach ($joinTypes as $index => $joinType){
            $alias = data_get($joinData, "aliases.{$index}");

            $join = collect([
                $joinData['relation'] . ($alias? "@$alias" : ''),
                $joinType
            ])->filter()->implode('|');

            /** @var SearchJoinRelationshipBuilder $joinBuilder */
            $joinBuilder = SearchJoinRelationshipBuilder::buildFromKeyAndValue($this->mockSearcher($from), $join, $joinData['values']);

            $this->assertNotNull($joinBuilder);

            $join = $alias? "{$joinData['table']} as {$alias}" : $joinData['table'];
            $joinContext = $alias? : $joinData['table'];

            $this->assertJoinBuilder($joinBuilder, [
                'join_instance_of' => JoinBelongsTo::class,
                'related_instance_of' => $joinData['model'],
                'method' => $joinType? data_get(static::$supportedJoins, $joinType, 'join') : 'join',
                'arguments' => [
                    [
                        'table' => $join,
                        'arguments' => [
                            "{$joinContext}.id",
                            '=',
                            "{$from['table']}.{$joinData['foreign_key']}"
                        ],
                    ],
                ]
            ]);
        }
    }
}
