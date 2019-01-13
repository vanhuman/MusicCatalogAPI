<?php

namespace Handlers;

use Helpers\TypeUtility;
use Models\Label;
use Models\Params;

class LabelsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'name'];
    private const MANDATORY_FIELDS = ['name'];

    private const SORT_FIELDS = ['id', 'name'];
    private const DEFAULT_SORT_FIELD = 'name';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
     * @param int $id
     * @throws \Exception
     * @return Label | Label[]
     */
    public function selectById(int $id)
    {
        if (!isset($id) || !TypeUtility::isInteger($id)) {
            $id = 0;
        }
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM label';
        $query .= ' WHERE id = ' . $id;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $object = [
            'query' => $query,
        ];
        if ($result->rowCount() === 0) {
            $label = null;
        } else {
            $labelData = $result->fetch();
            $label = $this->createModelFromDatabaseData($labelData);
        }
        $object['body'] = $label;
        return $object;
    }

    /**
     * @param Params $params
     * @throws \Exception
     * @return Label | Label[]
     */
    public function select(Params $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $page = $params->page;
        $pageSize = $params->pageSize;
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM label';
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
        $labelsData = $result->fetchAll();
        foreach ($labelsData as $labelData) {
            $newLabel = $this->createModelFromDatabaseData($labelData);
            $labels[] = $newLabel;
        }
        $labels = isset($labels) ? $labels : [];
        $object['body'] = $labels;
        return $object;
    }

    /**
     * @param array $labelData
     * @return Label
     * @throws \Exception
     */
    public function insert(array $labelData)
    {
        try {
            $this->validatePostData($labelData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForInsert($labelData);
        $query = 'INSERT INTO label (' . $postData['keys'] . ')';
        $query .= ' VALUES (' . $postData['values'] . ')';
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        $id = $this->getLastInsertedRecordId('label');
        return $this->selectById($id);
    }

    /**
     * @param int $id
     * @param array $labelData
     * @return Label
     * @throws \Exception
     */
    public function update(int $id, array $labelData)
    {
        try {
            $this->validatePostData($labelData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForUpdate($labelData);
        $query = 'UPDATE label SET ' . $postData . ' WHERE id = ' . $id;
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        return $this->selectById($id);
    }

    /**
     * @param array $labelData
     * @return Label
     */
    private function createModelFromDatabaseData(array $labelData)
    {
        $newLabel = new Label([
            'id' => $labelData['id'],
            'name' => $labelData['name'],
        ]);
        return $newLabel;
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