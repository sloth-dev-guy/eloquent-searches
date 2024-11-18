<?php

namespace Tests;

use SlothDevGuy\Searches\Search;
use Tests\database\Location;
use Tests\database\Person;

class SearchTest extends TestCase
{
    use WithDB;

    /**
     * this test are design to ensure the query builder syntax
     *
     * @return void
     */
    public function testJoins()
    {
        $this->loadTestModels();
        $this->migrate();

        $search = new Search(new Person(), [
            'addresses' => [
                'country' => [
                    'id|>' => 1,
                ],
                'tags' => [
                    'title' => 'foo-tag',
                ],
                'location' => [
                    'name' => 'bar-city'
                ],
            ],
            'tasks' => [
                'attachments' => [],
                'comments' => [
                    'likes|>' => 5,
                ],
                'title|_%' => 'foo-title',
                'complete' => true,
                'created_at|between' => ['2012-01-01', '2022-01-01'],
            ],
        ]);

        $this->assertNotNull($search->get());

        $sql = $search->builder()->toSql();

        //remove the quotes to more simple assertions
        $sql = str_replace(["'", '"', '`'], '', $sql);

        $whereFields = [
            'country.id',
            'tag.title',
            'location.name',
            'comments.likes',
            'tasks.title',
            'tasks.complete',
            'tasks.created_at',
        ];
        foreach ($whereFields as $whereField)
            $this->assertStringContainsString($whereField, $sql);

        $bindings = $search->builder()->getBindings();
        $wheres = [
            1,
            'foo-tag',
            'bar-city',
            5,
            'foo-title%',
            '2012-01-01',
            '2022-01-01',
        ];
        foreach ($wheres as $where)
            $this->assertContains($where, $bindings);
    }

    public function testJoinAndPagination()
    {
        $this->loadTestModels();
        $this->migrate();

        $search = new Search(new Person(), [
            'tasks' => [
                'comments' => [
                    'likes|>' => 5,
                ],
            ],
        ], null, [
            'max' => 1,
            'page' => 1,
        ]);

        $search->get();
        $pagination = $search->pagination();
        $this->assertNotNull($search->get());

        $this->assertEquals(1, $pagination['max']);
        $this->assertEquals(1, $pagination['page']);
    }

    public function testJoinRelationWithKebabSyntax()
    {
        $this->loadTestModels();
        $this->migrate();

        $search = eloquent_search(new Location(), [
            'administrative-division' => [

            ]
        ]);

        $this->assertNotNull($search->get());
        $sql = $search->builder()->toSql();

        $this->assertStringContainsString('administrative_division', $sql);
    }
}
