<?php

namespace Tests;

use Tests\database\Person;

class BuilderTests extends TestCase
{
    use WithDB;

    protected function testJoin()
    {
        $query = Person::query();

        $query->joinRelation('addresses', function ($query){
            $query->where('id', '>', 1);
        });

        $this->assertNotNull($query->get());
    }
}
