<?php

namespace Handlers;

abstract class DatabaseHandler {
    public const SORT_DIRECTION = ['ASC', 'DESC'];

    /* @var \PDO $db */
    protected $db;

    /**
     * Handler constructor.
     * @param $db
     */
    public function __construct($db) {
        $this->db = $db;
    }

    abstract public function get($id, $sortBy, $sortDirection);

    /**
     * @param string $table
     * @param int $id
     * @return int
     * @throws \Exception
     */
    public function delete($table, $id)
    {
        $query = 'DELETE FROM ' . $table . ' WHERE id = ' . $id;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        if ($result->rowCount() === 0) {
            throw new \Exception('ERROR: ' . ucfirst($table) . ' with id ' . $id . ' not found.', 404);
        }
        return $result->rowCount();
    }

    /**
     * @param $postData
     * @return mixed
     */
    protected function formatPostdataForInsert($postData)
    {
        foreach ($postData as $key => $value) {
            if ($key !== 'id') {
                $keys[] = $key;
                $values[] = $value;
            }
        }
        $formattedPostData['keys'] = implode($keys, ',');
        $formattedPostData['values'] = '"' . implode($values, '","') . '"';
        return $formattedPostData;
    }

    /**
     * @param $postData
     * @return bool | array
     */
    protected function formatPostdataForUpdate($postData)
    {
        foreach ($postData as $key => $value) {
            if ($key !== 'id') {
                $formattedPostData[] = $key . ' = "' . $value . '"';
            }
        }
        $formattedPostData = implode(',', $formattedPostData);
        return $formattedPostData;
    }

    /**
     * @param string $table
     * @return int
     * @throws \Exception
     */
    protected function getLastInsertedRecordId($table)
    {
        $query = 'SELECT * FROM ' . $table . ' ORDER BY id DESC LIMIT 1';
        try {
            $result = $this->db->query($query)->fetch();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        if (empty($result) || !array_key_exists('id', $result)) {
            throw new \Exception('Last inserted ' . $table . ' not found.', 500);
        }
        return $result['id'];
    }

}