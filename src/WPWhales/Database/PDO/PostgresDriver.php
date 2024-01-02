<?php

namespace WPWhales\Database\PDO;

use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use WPWhales\Database\PDO\Concerns\ConnectsToDatabase;

class PostgresDriver extends AbstractPostgreSQLDriver
{
    use ConnectsToDatabase;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_pgsql';
    }
}
