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
        $this->assertTrue($this->connection()->statement('SELECT 1'));
    }
}
