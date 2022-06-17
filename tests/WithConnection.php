<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Connection;

/**
 * Trait WithConnection
 * @package Tests
 */
trait WithConnection
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @return void
     */
    protected function bootConnection()
    {
        $container = new Container();

        $migration = require dirname(__FILE__) . '/database/boot_schema.php';
        $connection = $migration->boot($container);

        $migration->up();

        $this->connection = $connection;
        $this->container = $container;
    }

    /**
     * @return Connection
     */
    protected function connection()
    {
        return $this->connection;
    }
}
