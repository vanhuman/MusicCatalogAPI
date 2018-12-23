<?php

namespace Handlers;

abstract class Database {
    /* @var \PDO $db */
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }
}