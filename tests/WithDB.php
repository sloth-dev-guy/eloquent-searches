<?php

namespace Tests;

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

/**
 * Trait WithConnection
 * @package Tests
 */
trait WithDB
{
    /**
     * @var string
     */
    protected $connection;

    /**
     * @return ConnectionInterface|Connection
     */
    protected function connection()
    {
        /** @var ConnectionInterface|Connection $connection */
        $connection = DB::connection($this->connection);

        return $connection;
    }

    /**
     * @return void
     */
    protected function migrate()
    {
        $this->connection()->useDefaultSchemaGrammar();

        $schema = require __DIR__ . '/database/schema.php';

        $schema->up($schema->getBuilder($this->connection()));
    }
}
