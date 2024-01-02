<?php

namespace WPWhales\Database\PDO;

use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use WPWhales\Database\PDO\Concerns\ConnectsToDatabase;

class MySqlDriver extends AbstractMySQLDriver
{
    use ConnectsToDatabase;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_mysql';
    }
}
