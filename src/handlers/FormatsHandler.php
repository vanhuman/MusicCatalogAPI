<?php

namespace Handlers;

use Models\Format;

class FormatsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'name', 'description'];
    private const MANDATORY_FIELDS = ['name'];

    private const SORT_FIELDS = ['id', 'name'];
    private const DEFAULT_SORT_FIELD = 'id';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
     * @param array | int $params
     * @throws \Exception
     * @return Format | Format[]
     */
    public function get($params)
    {
        $id = $this->getIdFromParams($params);
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $page = $params['page'];
        $pageSize = $params['page_size'];
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM format';
        if (isset($id)) {
            $query .= ' WHERE id = ' . $id;
        }
        $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        $query .= ' LIMIT ' . ($pageSize * ($page - 1))  . ',' . $pageSize;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        if (isset($id)) {
            $formatData = $result->fetch();
            if ($result->rowCount() === 0) {
                throw new \Exception('ERROR: Format with id ' . $id . ' not found.', 500);
            }
            return $this->createModelFromDatabaseData($formatData);
        } else {
            $formatsData = $result->fetchAll();
            foreach ($formatsData as $formatData) {
                $newFormat = $this->createModelFromDatabaseData($formatData);
                $formats[] = $newFormat;
            }
            return isset($formats) ? $formats : [];
        }
    }

    /**
     * @param array $formatData
     * @return Format|Format[]
     * @throws \Exception
     */
    public function insertFormat($formatData)
    {
        try {
            $this->validatePostData($formatData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForInsert($formatData);
        $query = 'INSERT INTO format (' . $postData['keys'] . ')';
        $query .= ' VALUES (' . $postData['values'] . ')';
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        $id = $this->getLastInsertedRecordId('format');
        return $this->get($id);
    }

    /**
     * @param int $id
     * @param $formatData
     * @return Format|Format[]
     * @throws \Exception
     */
    public function updateFormat($id, $formatData)
    {
        try {
            $this->validatePostData($formatData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForUpdate($formatData);
        $query = 'UPDATE format SET ' . $postData . ' WHERE id = ' . $id;
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        return $this->get($id);
    }

    /**
     * @param $formatData
     * @return Format
     */
    private function createModelFromDatabaseData($formatData)
    {
        $newFormat = new Format([
            'id' => $formatData['id'],
            'name' => $formatData['name'],
            'description' => $formatData['description'],
        ]);
        return $newFormat;
    }

    /**
     * @param array $postData
     * @throws \Exception
     */
    private function validatePostData($postData)
    {
        try {
            $this->validateMandatoryFields($postData, self::MANDATORY_FIELDS);
            $this->validateKeys($postData, self::FIELDS);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}