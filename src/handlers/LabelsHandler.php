<?php

namespace Handlers;

use Models\Label;

class LabelsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'name'];
    private const SORT_FIELDS = ['id', 'name'];
    private const DEFAULT_SORT_FIELD = 'name';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
     * @param array $params
     * @throws \Exception
     * @return Label | Label[]
     */
    public function get($params)
    {
        $id = array_key_exists('id', $params) ? $params['id'] : null;
        if (!array_key_exists('sortBy', $params) || !in_array($params['sortBy'], self::SORT_FIELDS)) {
            $sortBy = self::DEFAULT_SORT_FIELD;
        } else {
            $sortBy = $params['sortBy'];
        }
        if (!array_key_exists('sortDirection', $params) || !in_array($params['sortDirection'], self::SORT_DIRECTION)) {
            $sortDirection = self::DEFAULT_SORT_DIRECTION;
        } else {
            $sortDirection = $params['sortDirection'];
        }
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
     * @param $labelData
     * @throws \Exception
     */
    private function validatePostData($labelData)
    {
        // name is mandatory
        if (!array_key_exists('name', $labelData)) {
            throw new \Exception('Name is a mandatory field.', 400);
        }
        // other keys than the database fields are not allowed
        foreach ($labelData as $key => $value) {
            if (!in_array($key, self::FIELDS)) {
                throw new \Exception($key . ' is not a valid field for this endpoint.', 400);
            }
        }
    }

}