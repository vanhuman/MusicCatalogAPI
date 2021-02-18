<?php

namespace Handlers;

use Helpers\TypeUtility;
use Models\GetParams;
use Models\Logging;

class LoggingHandler extends DatabaseHandler
{
    public static $FIELDS = [
        'fields' => ['id', 'type', 'date_created', 'user_id', 'data'],
        'mandatoryFields' => ['type'],
    ];

    /**
     * @throws \Exception
     * @return array
     */
    public function selectById(int $id)
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

    public function select(GetParams $params)
    {
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function insert(array $loggingData)
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

    /**
     * @return Logging
     */
    private function createModelFromDatabaseData(array $loggingData)
    {
        $newLogging = new Logging([
            'id' => $loggingData['id'],
            'type' => $loggingData['type'],
            'dateCreated' => $loggingData['date_created'],
            'userId' => $loggingData['user_id'],
            'date' => $loggingData['data'],
        ]);
        return $newLogging;
    }

    /**
     * @throws \Exception
     */
    private function validatePostData(array $postData)
    {
        $this->validateMandatoryFields($postData, self::$FIELDS['mandatoryFields']);
        $this->validateKeys($postData, self::$FIELDS['fields']);
    }

}
