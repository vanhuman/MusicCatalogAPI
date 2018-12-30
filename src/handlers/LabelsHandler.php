<?php

namespace Handlers;

use Models\Label;

class LabelsHandler extends Handler
{
    private const FIELDS = ['id', 'name'];
    private const SORT_FIELDS = ['id', 'name'];

    /**
     * @param int $labelId
     * @throws \Exception
     * @return Label $label | null
     */
    public function getLabel($labelId)
    {
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM label WHERE id = ' . $labelId;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $labelData = $result->fetch();
        if ($result->rowCount() === 0) {
            throw new \Exception('ERROR: Label with id ' . $labelId . ' not found.', 500);
        }
        return $this->createModelFromDatabaseData($labelData);
    }

    /**
     * @param string $sortBy
     * @param string $sortDirection
     * @return array
     * @throws \Exception
     */
    public function getLabels($sortBy = 'name', $sortDirection = 'ASC')
    {
        if (!in_array($sortBy, self::SORT_FIELDS)) {
            $sortBy = 'name';
        }
        if (!in_array($sortDirection, self::SORT_DIRECTION)) {
            $sortDirection = 'ASC';
        }
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM label';
        $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $labelsData = $result->fetchAll();
        foreach ($labelsData as $labelData) {
            $newLabel = $this->createModelFromDatabaseData($labelData);
            $labels[] = $newLabel;
        }
        return isset($labels) ? $labels : [];
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
        $labelId = $this->getLastInsertedRecordId('label');
        return $this->getLabel($labelId);
    }

    /**
     * @param int $labelId
     * @param $labelData
     * @return Label
     * @throws \Exception
     */
    public function updateLabel($labelId, $labelData)
    {
        try {
            $this->validatePostData($labelData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForUpdate($labelData);
        $query = 'UPDATE label SET ' . $postData . ' WHERE id = ' . $labelId;
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        return $this->getLabel($labelId);
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