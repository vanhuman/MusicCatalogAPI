<?php

namespace Handlers;

use Helpers\TypeUtility;
use Models\Label;
use Models\GetParams;

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
        $result = $this->db->query($query);
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
     * @param GetParams $params
     * @throws \Exception
     * @return Label | Label[]
     */
    public function select(GetParams $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $page = $params->page;
        $pageSize = $params->pageSize;

        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM label';
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
        $this->validatePostData($labelData);
        $postData = $this->formatPostdataForInsert($labelData);
        $query = 'INSERT INTO label (' . $postData['keys'] . ')';
        $query .= ' VALUES (' . $postData['values'] . ')';
        $this->db->query($query);
        $id = $this->db->lastInsertId();
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
        $this->validatePostData($labelData);
        $query = 'SELECT id FROM label WHERE id = ' . $id;
        if ($this->db->query($query)->rowCount() === 0) {
            return null;
        }
        $postData = $this->formatPostdataForUpdate($labelData);
        $query = 'UPDATE label SET ' . $postData . ' WHERE id = ' . $id;
        $this->db->query($query);
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
        $this->validateMandatoryFields($postData, self::MANDATORY_FIELDS);
        $this->validateKeys($postData, self::FIELDS);
    }

}