<?php

namespace Handlers;

use Helpers\DatabaseConnection;
use Models\GetParams;

abstract class DatabaseHandler extends DatabaseConnection
{
    public const SORT_DIRECTIONS = ['ASC', 'DESC'];

    abstract public function selectById(int $id);

    abstract public function select(GetParams $params);

    abstract public function insert(array $body);

    abstract public function update(int $id, array $body);

    /**
     * Generic delete function to handle all delete requests.
     * @return int
     * @throws \Exception
     */
    public function delete(string $table, int $id)
    {
        $query = 'DELETE FROM ' . $table . ' WHERE id = ' . $id;
        $result = $this->db->query($query);
        if ($result->rowCount() === 0) {
            throw new \Exception(ucfirst($table) . ' with id ' . $id . ' not found.', 404);
        }
        return $result->rowCount();
    }

    /**
     * Return arrays for keys and values to be used in an SQL INSERT statement.
     * @return array
     */
    protected function formatPostdataForInsert(array $postData)
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
     * @return string
     */
    protected function formatPostdataForUpdate(array $postData)
    {
        $formattedPostData = [];
        foreach ($postData as $key => $value) {
            if ($key !== 'id') {
                $formattedPostData[] = $key . ' = "' . $value . '"';
            }
        }
        $formattedPostData = implode(',', $formattedPostData);
        return $formattedPostData;
    }

    /**
     * Get the sortby parameter or get the default.
     * @return string
     */
    protected function getSortByFromParams(GetParams $params, array $sortFields, string $defaultSortField)
    {
        if (!in_array($params->sortBy, $sortFields)) {
            return $defaultSortField;
        } else {
            return $params->sortBy;
        }
    }

    /**
     * Get the sortdirection parameter or get the default.
     * @return string
     */
    protected function getSortDirectionFromParams(GetParams $params, string $defaultSortDirection)
    {
        if (!in_array(strtoupper($params->sortDirection), self::SORT_DIRECTIONS)) {
            return $defaultSortDirection;
        } else {
            return strtoupper($params->sortDirection);
        }
    }

    /**
     * Validate fields indicated in the specific handlers as mandatory.
     * @throws \Exception
     */
    protected function validateMandatoryFields(array $postData, array $mandatoryFields)
    {
        foreach($mandatoryFields as $field) {
            if (!array_key_exists($field, $postData)) {
                throw new \Exception(ucfirst($field) . ' is a mandatory field.', 400);
            }
        }
    }

    /**
     * Validate that the postdata only holds permitted fields.
     * @throws \Exception
     */
    protected function validateKeys(array $postData, array $fields)
    {
        // other keys than the database fields are not allowed
        foreach ($postData as $key => $value) {
            if (!in_array($key, $fields)) {
                throw new \Exception($key . ' is not a valid field for this endpoint.', 400);
            }
        }
    }
    
}