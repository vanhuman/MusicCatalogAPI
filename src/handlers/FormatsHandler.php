<?php

namespace Handlers;

use Helpers\TypeUtility;
use Models\Format;
use Models\Params;

class FormatsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'name', 'description'];
    private const MANDATORY_FIELDS = ['name'];

    private const SORT_FIELDS = ['id', 'name'];
    private const DEFAULT_SORT_FIELD = 'id';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
     * @param int $id
     * @throws \Exception
     * @return Format | Format[]
     */
    public function selectById(int $id)
    {
        if (!isset($id) || !TypeUtility::isInteger($id)) {
            $id = 0;
        }
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM format';
        $query .= ' WHERE id = ' . $id;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $object = [
            'query' => $query,
        ];
        $formatData = $result->fetch();
        if ($result->rowCount() === 0) {
            $format = null;
        } else {
            $format = $this->createModelFromDatabaseData($formatData);
        }
        $object['body'] = $format;
        return $object;
    }

    /**
     * @param Params $params
     * @throws \Exception
     * @return Format | Format[]
     */
    public function select(Params $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $page = $params->page;
        $pageSize = $params->pageSize;
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM format';
        $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        $queryWithoutLimit = $query;
        $query .= ' LIMIT ' . ($pageSize * ($page - 1)) . ',' . $pageSize;
        try {
            $result = $this->db->query($query);
            $resultWithoutLimit = $this->db->query($queryWithoutLimit);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $object = [
            'total_number_of_records' => $resultWithoutLimit->rowCount(),
            'query' => $query,
            'sortby' => $sortBy,
            'sortdirection' => $sortDirection,
        ];
        $formatsData = $result->fetchAll();
        foreach ($formatsData as $formatData) {
            $newFormat = $this->createModelFromDatabaseData($formatData);
            $formats[] = $newFormat;
        }
        $formats = isset($formats) ? $formats : [];
        $object['body'] = $formats;
        return $object;
    }

    /**
     * @param array $formatData
     * @return Format|Format[]
     * @throws \Exception
     */
    public function insert(array $formatData)
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
        return $this->selectById($id);
    }

    /**
     * @param int $id
     * @param array $formatData
     * @return Format|Format[]
     * @throws \Exception
     */
    public function update(int $id, array $formatData)
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
        return $this->selectById($id);
    }

    /**
     * @param array $formatData
     * @return Format
     */
    private function createModelFromDatabaseData(array $formatData)
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
    private function validatePostData(array $postData)
    {
        try {
            $this->validateMandatoryFields($postData, self::MANDATORY_FIELDS);
            $this->validateKeys($postData, self::FIELDS);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}