<?php

namespace Handlers;

use Models\Label;

class LabelsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'name'];
    private const MANDATORY_FIELDS = ['name'];

    private const SORT_FIELDS = ['id', 'name'];
    private const DEFAULT_SORT_FIELD = 'name';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
     * @param array | int $params
     * @throws \Exception
     * @return Label | Label[]
     */
    public function get($params)
    {
        $id = $this->getIdFromParams($params);
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM label';
        if (isset($id)) {
            $query .= ' WHERE id = ' . $id;
        } else {
            $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        }
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        if (isset($id)) {
            $labelData = $result->fetch();
            if ($result->rowCount() === 0) {
                throw new \Exception('ERROR: Label with id ' . $id . ' not found.', 500);
            }
            return $this->createModelFromDatabaseData($labelData);
        } else {
            $labelsData = $result->fetchAll();
            foreach ($labelsData as $labelData) {
                $newLabel = $this->createModelFromDatabaseData($labelData);
                $labels[] = $newLabel;
            }
            return isset($labels) ? $labels : [];
        }
    }

    /**
     * @param $labelData
     * @return Label
     * @throws \Exception
     */
    public function insertLabel($labelData)
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
        return $this->get($id);
    }

    /**
     * @param int $id
     * @param $labelData
     * @return Label
     * @throws \Exception
     */
    public function updateLabel($id, $labelData)
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
        return $this->get($id);
    }

    /**
     * @param $labelData
     * @return Label
     */
    private function createModelFromDatabaseData($labelData)
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