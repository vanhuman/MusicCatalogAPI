<?php

namespace Handlers;

use Helpers\TypeUtility;
use Models\Format;
use Models\GetParams;

class FormatsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'name', 'description'];
    private const MANDATORY_FIELDS = ['name'];

    private const SORT_FIELDS = ['id', 'name'];
    private const DEFAULT_SORT_FIELD = 'id';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
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
        $result = $this->db->query($query);
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
     * @throws \Exception
     * @return Format | Format[]
     */
    public function select(GetParams $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $page = $params->page;
        $pageSize = $params->pageSize;

        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM format';
        $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        $queryWithoutLimit = $query;
        $query .= ' LIMIT ' . ($pageSize * ($page - 1)) . ',' . $pageSize;

        $result = $this->db->query($query);
        $resultWithoutLimit = $this->db->query($queryWithoutLimit);
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
     * @return Format|Format[]
     * @throws \Exception
     */
    public function insert(array $formatData)
    {
        $this->validatePostData($formatData);
        $postData = $this->formatPostdataForInsert($formatData);
        $query = 'INSERT INTO format (' . $postData['keys'] . ')';
        $query .= ' VALUES (' . $postData['values'] . ')';
        $this->db->query($query);
        $id = $this->db->lastInsertId();
        return $this->selectById($id);
    }

    /**
     * @return Format|Format[]
     * @throws \Exception
     */
    public function update(int $id, array $formatData)
    {
        $this->validatePostData($formatData);
        $query = 'SELECT id FROM format WHERE id = ' . $id;
        if ($this->db->query($query)->rowCount() === 0) {
            return null;
        }
        $postData = $this->formatPostdataForUpdate($formatData);
        $query = 'UPDATE format SET ' . $postData . ' WHERE id = ' . $id;
        $this->db->query($query);
        return $this->selectById($id);
    }

    /**
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
     * @throws \Exception
     */
    private function validatePostData(array $postData)
    {
        $this->validateMandatoryFields($postData, self::MANDATORY_FIELDS);
        $this->validateKeys($postData, self::FIELDS);
    }
}