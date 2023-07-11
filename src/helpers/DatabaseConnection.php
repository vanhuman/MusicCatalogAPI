<?php

namespace Helpers;

use PDO;

class DatabaseConnection
{
    /* @var PDO $db */
    protected $db;

    public function __construct()
    {
        $this->setDatabase();
    }

    public function getDatabase(): PDO
    {
        return $this->db;
    }

    private function setDatabase()
    {
        /* @var array $settings */
        include 'settings.php';
        $env = getenv('ENVIRONMENT') === 'development' ? 'development' : 'production';
        $settings = $settings[$env];

        $options = [
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO(
            'mysql:host=' . $settings['dbhost'] . ';dbname=' . $settings['dbname'],
            $settings['dbuser'],
            $settings['dbpassword'],
            $options
        );

        $this->db = $pdo;
    }
}
