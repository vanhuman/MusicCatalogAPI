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
     * @return array
     */
    public function selectById(int $id, array $object = [])
    {
        if (!isset($id) || !TypeUtility::isInteger($id)) {
            $id = 0;
        }
        $query = 'SELECT ' . implode(self::$FIELDS['fields'], ',') . ' FROM label';
        $query .= ' WHERE id = ' . $id;
        $result = $this->db->query($query);
        if (empty($object['query'])) {
            $object['query'] = $query;
        }
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
        if ($sortBy !== 'id') {
            $query .= ', id ' . $sortDirection;
        }
        $queryWithoutLimit = $query;
        if ($page !== 0) {
            $query .= ' LIMIT ' . ($pageSize * ($page - 1)) . ',' . $pageSize;
        }

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
     * @return array
     * @throws \Exception
     */
    public function insert(array $labelData)
    {
        $this->validatePostData($labelData);
        $postData = $this->formatPostdataForInsert($labelData);
        $statement = $this->db->prepare('INSERT INTO label (' . $postData['keys'] . ') VALUES (' . $postData['variables'] . ')');
        $statement->execute($postData['data']);
        $id = $this->db->lastInsertId();
        $object['query'] = $this->buildQuery($statement->queryString, $postData['data']) . ' - insert ID: ' . $id;
        return $this->selectById($id, $object);
    }

    /**
     * @return array
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
        $statement = $this->db->prepare('UPDATE label SET ' . $postData['keys_variables'] . ' WHERE id = ' . $id);
        $statement->execute($postData['data']);
        $object['query'] = $this->buildQuery($statement->queryString, $postData['data']);
        return $this->selectById($id, $object);
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
