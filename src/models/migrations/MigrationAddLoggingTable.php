<?php

namespace Models\migrations;

use PDO;

class MigrationAddLoggingTable implements MigrationInterface
{

    /* @var PDO $db */
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function run() {
        $query = 'CREATE TABLE `music_catalog`.`logging` ( ' .
            '`id` INT(11) NOT NULL AUTO_INCREMENT , ' .
            '`type` ENUM("authentication","query") NOT NULL , ' .
            '`created` DATETIME NOT NULL , ' .
            '`user_id` INT(11) NOT NULL , ' .
            '`data` VARCHAR(2048) NULL , PRIMARY KEY (`id`) ' .
            ')';
        $this->db->query($query);

    }
}
