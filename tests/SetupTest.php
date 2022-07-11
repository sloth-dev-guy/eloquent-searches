<?php

namespace Tests;

/**
 * Class SetupTest
 * @package Tests
 */
class SetupTest extends TestCase
{
    use WithDB, WithFaker;

    /**
     * Test all the configurations and database setup.
     *
     * @return void
     */
    public function testApp()
    {
        $this->migrate();

        $this->assertNotEmpty($this->faker()->name);

        $tables = $this->connection()->table('sqlite_schema')
            ->where('type', 'table')
            ->where('name', 'not like', 'sqlite_%')
            ->get();

        $this->assertGreaterThan(0, $tables->count());
    }
}
