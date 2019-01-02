<?php

namespace Handlers;

use Models\Genre;

class GenresHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'description', 'notes'];
    private const SORT_FIELDS = ['id', 'description'];
    private const DEFAULT_SORT_FIELD = 'id';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
     * @param array $params
     * @throws \Exception
     * @return Genre | Genre[]
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
                throw new \Exception('ERROR: Genre with id ' . $id . ' not found.', 500);
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
     * @throws \Exception
     */
    public function insertGenre($genreData)
    {
        try {
            $this->validatePostData($genreData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForInsert($genreData);
        $query = 'INSERT INTO genre (' . $postData['keys'] . ')';
        $query .= ' VALUES (' . $postData['values'] . ')';
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        $id = $this->getLastInsertedRecordId('genre');
        return $this->get($id);
    }

    /**
     * @param $id
     * @param $genreData
     * @return Genre
     * @throws \Exception
     */
    public function updateGenre($id, $genreData)
    {
        try {
            $this->validatePostData($genreData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForUpdate($genreData);
        $query = 'UPDATE genre SET ' . $postData . ' WHERE id = ' . $id;
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        return $this->get($id);
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

    /**
     * @param $genreData
     * @throws \Exception
     */
    private function validatePostData($genreData)
    {
        // name is mandatory
        if (!array_key_exists('description', $genreData)) {
            throw new \Exception('Description is a mandatory field.', 400);
        }
        // other keys than the database fields are not allowed
        foreach ($genreData as $key => $value) {
            if (!in_array($key, self::FIELDS)) {
                throw new \Exception($key . ' is not a valid field for this endpoint.', 400);
            }
        }
    }

}