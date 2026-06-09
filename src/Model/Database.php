<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class Database
{
    private Connection $connection;

    public function __construct()
    {
        $this->connection = DriverManager::getConnection([
            'driver'   => 'pdo_mysql',
            'host'     => $_ENV['DB_HOST'],
            'port'     => (int) $_ENV['DB_PORT'],
            'dbname'   => $_ENV['DB_NAME'],
            'user'     => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
            'charset'  => 'utf8mb4',
        ]);
    }

    public function get(): Connection
    {
        return $this->connection;
    }
}
