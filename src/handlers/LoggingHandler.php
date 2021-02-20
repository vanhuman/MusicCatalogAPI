<?php

namespace Handlers;

use Exception;
use Helpers\TypeUtility;
use Models\GetParams;
use Models\Logging;

class LoggingHandler extends DatabaseHandler
{
    public static $FIELDS = [
        'fields' => ['id', 'type', 'date_created', 'user_id', 'ip_address', 'data'],
        'mandatoryFields' => ['type'],
    ];

    /**
     * @throws Exception
     */
    public function selectById(int $id): array
    {
        if (!isset($id) || !TypeUtility::isInteger($id)) {
            $id = 0;
        }
        $query = 'SELECT ' . implode(self::$FIELDS['fields'], ',') . ' FROM logging';
        $query .= ' WHERE id = ' . $id;
        $result = $this->db->query($query);
        $object = [
            'query' => $query,
        ];
        if ($result->rowCount() === 0) {
            $logging = null;
        } else {
            $loggingData = $result->fetch();
            $logging = $this->createModelFromDatabaseData($loggingData);
        }
        $object['body'] = $logging;
        return $object;
    }

    public function select(GetParams $params): void
    {
    }

    /**
     * @return Logging[]
     */
    public function selectByIP(string $ipAddress): array
    {
        $query = 'SELECT ' . implode(self::$FIELDS['fields'], ',') . ' FROM logging';
        $query .= ' WHERE ip_address = "' . $ipAddress . '"';
        $result = $this->db->query($query);
        $loggingsData = $result->fetchAll();
        foreach ($loggingsData as $loggingData) {
            $newLogging = $this->createModelFromDatabaseData($loggingData);
            $loggings[] = $newLogging;
        }
        $loggings = isset($loggings) ? $loggings : [];
        return $loggings;
    }
    
    /**
     * @throws Exception
     */
    public function insert(array $loggingData): array
    {
        $this->validatePostData($loggingData);
        $postData = $this->formatPostdataForInsert($loggingData);
        $statement = $this->db->prepare('INSERT INTO logging (' . $postData['keys'] . ') VALUES (' . $postData['variables'] . ')');
        $statement->execute($postData['data']);
        $id = $this->db->lastInsertId();
        return $this->selectById($id);
    }

    public function update(int $id, array $body)
    {
    }

    private function createModelFromDatabaseData(array $loggingData): Logging
    {
        $newLogging = new Logging([
            'id' => $loggingData['id'],
            'type' => $loggingData['type'],
            'dateCreated' => $loggingData['date_created'],
            'userId' => $loggingData['user_id'],
            'ipAddress' => $loggingData['ip_address'],
            'data' => $loggingData['data'],
        ]);
        return $newLogging;
    }

    /**
     * @throws Exception
     */
    private function validatePostData(array $postData): void
    {
        $this->validateMandatoryFields($postData, self::$FIELDS['mandatoryFields']);
        $this->validateKeys($postData, self::$FIELDS['fields']);
    }

}
