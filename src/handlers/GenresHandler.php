<?php

namespace Handlers;

use Models\Genre;

class GenresHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'description', 'notes'];
    private const MANDATORY_FIELDS = ['description'];

    private const SORT_FIELDS = ['id', 'description'];
    private const DEFAULT_SORT_FIELD = 'id';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
     * @param array | int $params
     * @throws \Exception
     * @return Genre | Genre[]
     */
    public function get($params)
    {
        $id = $this->getIdFromParams($params);
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM genre';
        if (isset($id)) {
            $query .= ' WHERE id = ' . $id;
        }
        $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        if (isset($id)) {
            if ($result->rowCount() === 0) {
                throw new \Exception('ERROR: Genre with id ' . $id . ' not found.', 500);
            }
            $genreData = $result->fetch();
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
     * @param array $genreData
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