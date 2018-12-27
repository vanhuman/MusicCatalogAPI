<?php

namespace Handlers;

use Models\Format;

class FormatsHandler extends Database
{
    const FIELDS = ['id', 'name', 'description'];

    /**
     * @param int $formatId
     * @throws \Exception
     * @return Format $format
     */
    public function getFormat($formatId)
    {
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM format WHERE id = ' . $formatId;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $formatData = $result->fetch();
        return new Format($formatData);
    }
}