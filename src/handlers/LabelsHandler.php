<?php

namespace Handlers;

use Models\Label;

class LabelsHandler extends Database
{
    const FIELDS = ['id', 'name'];

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
        if (!$labelData) {
            return null;
        }
        return new Label($labelData);
    }
}