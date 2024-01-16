<?php

namespace WPWCore\Database;

use Exception;
use PDO;
use WPWCore\Filesystem\Filesystem;
use WPWCore\Database\PDO\MySqlDriver;
use WPWCore\Database\Query\Grammars\MySqlGrammar as QueryGrammar;
use WPWCore\Database\Query\Processors\MySqlProcessor;
use WPWCore\Database\Schema\Grammars\MySqlGrammar as SchemaGrammar;
use WPWCore\Database\Schema\MySqlBuilder;
use WPWCore\Database\Schema\MySqlSchemaState;

class MySqlConnection extends Connection
{
    /**
     * Escape a binary value for safe SQL embedding.
     *
     * @param  string  $value
     * @return string
     */
    protected function escapeBinary($value)
    {
        $hex = bin2hex($value);

        return "x'{$hex}'";
    }

    /**
     * Determine if the given database exception was caused by a unique constraint violation.
     *
     * @param  \Exception  $exception
     * @return bool
     */
    protected function isUniqueConstraintError(Exception $exception)
    {
        return boolval(preg_match('#Integrity constraint violation: 1062#i', $exception->getMessage()));
    }

    /**
     * Determine if the connected database is a MariaDB database.
     *
     * @return bool
     */
    public function isMaria()
    {
        return str_contains($this->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION), 'MariaDB');
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \WPWCore\Database\Query\Grammars\MySqlGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        ($grammar = new QueryGrammar)->setConnection($this);

        return $this->withTablePrefix($grammar);
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \WPWCore\Database\Schema\MySqlBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new MySqlBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \WPWCore\Database\Schema\Grammars\MySqlGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        ($grammar = new SchemaGrammar)->setConnection($this);

        return $this->withTablePrefix($grammar);
    }

    /**
     * Get the schema state for the connection.
     *
     * @param  \WPWCore\Filesystem\Filesystem|null  $files
     * @param  callable|null  $processFactory
     * @return \WPWCore\Database\Schema\MySqlSchemaState
     */
    public function getSchemaState(Filesystem $files = null, callable $processFactory = null)
    {
        return new MySqlSchemaState($this, $files, $processFactory);
    }

    /**
     * Get the default post processor instance.
     *
     * @return \WPWCore\Database\Query\Processors\MySqlProcessor
     */
    protected function getDefaultPostProcessor()
    {
        return new MySqlProcessor;
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \WPWCore\Database\PDO\MySqlDriver
     */
    protected function getDoctrineDriver()
    {
        return new MySqlDriver;
    }
}
