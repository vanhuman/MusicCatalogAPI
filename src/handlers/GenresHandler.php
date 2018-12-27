<?php

namespace Handlers;

use Models\Genre;

class GenresHandler extends Database
{
    const FIELDS = ['id', 'description', 'notes'];

    /**
     * @param int $genreId
     * @throws \Exception
     * @return Genre $genre | null
     */
    public function getGenre($genreId)
    {
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM genre WHERE id = ' . $genreId;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $genreData = $result->fetch();
        if (!$genreData) {
            return null;
        }
        return new Genre($genreData);
    }
}