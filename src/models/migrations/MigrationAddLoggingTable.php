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
            '`type` ENUM("Authentication","Query","Error") NOT NULL , ' .
            '`date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ' .
            '`user_id` INT(11) NULL , ' .
            '`ip_address` VARCHAR(256) NULL , ' .
            '`data` VARCHAR(2048) NULL , PRIMARY KEY (`id`) ' .
            ')';
        $this->db->query($query);

    }
}
