<?php

namespace Handlers;

use Models\Genre;

class GenresHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'description', 'notes'];
    private const SORT_FIELDS = ['id', 'description'];

    /**
     * @param int $id
     * @param string $sortBy
     * @param string $sortDirection
     * @throws \Exception
     * @return Genre $genre | null
     */
    public function get($id, $sortBy = 'id', $sortDirection = 'ASC')
    {
        if (!in_array($sortBy, self::SORT_FIELDS)) {
            $sortBy = 'id';
        }
        if (!in_array($sortDirection, self::SORT_DIRECTION)) {
            $sortDirection = 'ASC';
        }
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM genre';
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
            $genreData = $result->fetch();
            if ($result->rowCount() === 0) {
                throw new \Exception('ERROR: Label with id ' . $id . ' not found.', 500);
            }
            return $this->createModelFromDatabaseData($genreData);
        } else {
            $genresData = $result->fetchAll();
            foreach ($genresData as $genreData) {
                $newGenre = $this->createModelFromDatabaseData($genreData);
                $genres[] = $newGenre;
            }
            return isset($genres) ? $genres : [];
        }
    }
    
    /**
     * @param $genreData
     * @return Genre
     */
    private function createModelFromDatabaseData($genreData)
    {
        $newGenre = new Genre([
            'id' => $genreData['id'],
            'description' => $genreData['description'],
            'notes' => $genreData['notes'],
        ]);
        return $newGenre;
    }
}