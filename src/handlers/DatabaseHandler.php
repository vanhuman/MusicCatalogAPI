<?php

namespace Handlers;

use Helpers\DatabaseConnection;

abstract class DatabaseHandler extends DatabaseConnection
{
    public const SORT_DIRECTION = ['ASC', 'DESC'];

    abstract public function select($params);

    abstract public function insert($body);

    abstract public function update($id, $body);

    /**
     * Generic delete function to handle all delete requests.
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
     * Return arrays for keys and values to be used in an SQL INSERT statement.
     * @param array $postData
     * @return array
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
     * Return string with key=value pairs, to be used in an SQL UPDATE statement.
     * @param array $postData
     * @return string
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
     * Get id from last inserted record in the specified table.
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

    /**
     * Get the id entry from $params. In some cases $params is updated.
     * If $params is not set, it is assigned an empty array.
     * If $params is not an array, we assume it is the id of a record. Set $params to [];
     * @param array | int $params
     * @return int
     */
    protected function getIdFromParams(&$params)
    {
        if (!isset($params)) {
            $id = 0;
            $params = [];
        } else if (!is_array($params)) {
            $id = $params;
            $params = [];
        } else {
            $id = array_key_exists('id', $params) ? $params['id'] : 0;
        }
        return $id;
    }

    /**
     * Get the sortby parameter or get the default.
     * @param array $params
     * @param array $sortFields
     * @param string $defaultSortField
     * @return string
     */
    protected function getSortByFromParams($params, $sortFields, $defaultSortField)
    {
        if (!array_key_exists('sortby', $params) || !in_array($params['sortby'], $sortFields)) {
            return $defaultSortField;
        } else {
            return $params['sortby'];
        }
    }

    /**
     * Get the sortdirection parameter or get the default.
     * @param array $params
     * @param string $defaultSortDirection
     * @return string
     */
    protected function getSortDirectionFromParams($params, $defaultSortDirection)
    {
        if (!array_key_exists('sortdirection', $params)
            || !in_array(strtoupper($params['sortdirection']), self::SORT_DIRECTION)) {
            return $defaultSortDirection;
        } else {
            return strtoupper($params['sortdirection']);
        }
    }

    /**
     * Validate fields indicated in the specific handlers as mandatory.
     * @param array $postData
     * @param array $mandatoryFields
     * @throws \Exception
     */
    protected function validateMandatoryFields($postData, $mandatoryFields)
    {
        foreach($mandatoryFields as $field) {
            if (!array_key_exists($field, $postData)) {
                throw new \Exception(ucfirst($field) . ' is a mandatory field.', 400);
            }
        }
    }

    /**
     * Validate that the postdata only holds permitted fields.
     * @param array $postData
     * @param array $fields
     * @throws \Exception
     */
    protected function validateKeys($postData, $fields)
    {
        // other keys than the database fields are not allowed
        foreach ($postData as $key => $value) {
            if (!in_array($key, $fields)) {
                throw new \Exception($key . ' is not a valid field for this endpoint.', 400);
            }
        }
    }
    
}