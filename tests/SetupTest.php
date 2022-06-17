<?php

namespace Tests;

/**
 * Class SetupTest
 * @package Tests
 */
class SetupTest extends TestCase
{
    use WithConnection, WithFaker;

    /**
     * Test all the configurations and database setup.
     *
     * @return void
     */
    public function testApp()
    {
        $this->assertNotEmpty($this->faker()->name);

        $tables = $this->connection()->table('sqlite_schema')
            ->where('type', 'table')
            ->where('name', 'not like', 'sqlite_%')
            ->get();

        $this->assertEquals(7, $tables->count());
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->bootConnection();
    }
}
