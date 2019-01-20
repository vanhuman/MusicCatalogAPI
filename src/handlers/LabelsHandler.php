<?php

namespace Handlers;

use Helpers\TypeUtility;
use Models\Label;
use Models\GetParams;

class LabelsHandler extends DatabaseHandler
{
    public static $FIELDS = [
        'fields' => ['id', 'name'],
        'mandatoryFields' => ['name'],
        'sortFields' => ['id', 'name'],
        'sortDirections' => parent::SORT_DIRECTIONS,
        'defaultSortField' => 'name',
        'defaultSortDirection' => 'ASC',
    ];

    /**
     * @throws \Exception
     * @return Label | Label[]
     */
    public function selectById(int $id)
    {
        if (!isset($id) || !TypeUtility::isInteger($id)) {
            $id = 0;
        }
        $query = 'SELECT ' . implode(self::$FIELDS['fields'], ',') . ' FROM label';
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
     * @throws \Exception
     * @return Label | Label[]
     */
    public function select(GetParams $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::$FIELDS['sortFields'], self::$FIELDS['defaultSortField']);
        $sortDirection = $this->getSortDirectionFromParams($params, self::$FIELDS['defaultSortDirection']);
        $page = $params->page;
        $pageSize = $params->pageSize;

        $query = 'SELECT ' . implode(self::$FIELDS['fields'], ',') . ' FROM label';
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
     * @throws \Exception
     */
    private function validatePostData(array $postData)
    {
        $this->validateMandatoryFields($postData, self::$FIELDS['mandatoryFields']);
        $this->validateKeys($postData, self::$FIELDS['fields']);
    }

}