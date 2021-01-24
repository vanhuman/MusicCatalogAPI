<?php

namespace Helpers;

use PDO;

class DatabaseConnection
{
    /* @var PDO $db */
    protected $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }
}