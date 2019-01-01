<?php

namespace Handlers;

use Models\Format;

class FormatsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'name', 'description'];
    private const SORT_FIELDS = ['id', 'name'];
    private const DEFAULT_SORT_FIELD = 'id';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
     * @param int $id
     * @param string $sortBy
     * @param string $sortDirection
     * @throws \Exception
     * @return Format | Format[]
     */
    public function get($id, $sortBy = self::DEFAULT_SORT_FIELD, $sortDirection = self::DEFAULT_SORT_DIRECTION)
    {
        if (!in_array($sortBy, self::SORT_FIELDS)) {
            $sortBy = self::DEFAULT_SORT_FIELD;
        }
        if (!in_array($sortDirection, self::SORT_DIRECTION)) {
            $sortDirection = self::DEFAULT_SORT_DIRECTION;
        }
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM format';
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
     * @param $formatData
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
     * @param $id
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
     * @param $formatData
     * @throws \Exception
     */
    private function validatePostData($formatData)
    {
        // name is mandatory
        if (!array_key_exists('name', $formatData)) {
            throw new \Exception('Name is a mandatory field.', 400);
        }
        // other keys than the database fields are not allowed
        foreach ($formatData as $key => $value) {
            if (!in_array($key, self::FIELDS)) {
                throw new \Exception($key . ' is not a valid field for this endpoint.', 400);
            }
        }
    }
}