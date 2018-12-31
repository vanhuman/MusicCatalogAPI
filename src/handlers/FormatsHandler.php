<?php

namespace Handlers;

use Models\Format;

class FormatsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'name', 'description'];
    private const SORT_FIELDS = ['id', 'name'];

    /**
     * @param int $id
     * @param string $sortBy
     * @param string $sortDirection
     * @throws \Exception
     * @return Format | Format[]
     */
    public function get($id, $sortBy = 'id', $sortDirection = 'ASC')
    {
        if (!in_array($sortBy, self::SORT_FIELDS)) {
            $sortBy = 'id';
        }
        if (!in_array($sortDirection, self::SORT_DIRECTION)) {
            $sortDirection = 'ASC';
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
                throw new \Exception('ERROR: Label with id ' . $id . ' not found.', 500);
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
}