<?php

namespace Handlers;

use Helpers\DatabaseConnection;

abstract class DatabaseHandler extends DatabaseConnection
{
    public const SORT_DIRECTION = ['ASC', 'DESC'];

    abstract public function get($params);

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
     * @param array $postData
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
     * @param array $postData
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

    /**
     * @param array | int $params
     * @return mixed | null
     */
    protected function getIdFromParams(&$params)
    {
        if (!is_array($params)) {
            $id = $params;
            $params = [];
        } else {
            $id = array_key_exists('id', $params) ? $params['id'] : null;
        }
        return $id;
    }

    /**
     * @param array $params
     * @param array $sortFields
     * @param string $defaultSortField
     * @return string
     */
    protected function getSortByFromParams($params, $sortFields, $defaultSortField)
    {
        if (!array_key_exists('sortBy', $params) || !in_array($params['sortBy'], $sortFields)) {
            $sortBy = $defaultSortField;
        } else {
            $sortBy = $params['sortBy'];
        }
        return $sortBy;
    }

    /**
     * @param array $params
     * @param string $defaultSortDirection
     * @return string
     */
    protected function getSortDirectionFromParams($params, $defaultSortDirection)
    {
        if (!array_key_exists('sortDirection', $params) || !in_array($params['sortDirection'], self::SORT_DIRECTION)) {
            $sortDirection = $defaultSortDirection;
        } else {
            $sortDirection = $params['sortDirection'];
        }
        return $sortDirection;
    }
}