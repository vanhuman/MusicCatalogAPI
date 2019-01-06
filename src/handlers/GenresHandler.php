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
     * @return array
     */
    public function select($params)
    {
        $id = $this->getIdFromParams($params);
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $page = array_key_exists('page', $params) ? $params['page'] : 1;
        $pageSize = array_key_exists('page_size', $params) ? $params['page_size'] : 50;
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM genre';
        if (isset($id)) {
            $query .= ' WHERE id = ' . $id;
        } else {
            $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
            $queryWithoutLimit = $query;
            $query .= ' LIMIT ' . ($pageSize * ($page - 1)) . ',' . $pageSize;
        }
        try {
            $result = $this->db->query($query);
            if (isset($queryWithoutLimit)) {
                $resultWithoutLimit = $this->db->query($queryWithoutLimit);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $totalRecords = isset($queryWithoutLimit) ? $resultWithoutLimit->rowCount() : 1;
        $object = [
            'total_number_of_records' => $totalRecords,
            'query' => $query,
            'sortby' => $sortBy,
            'sortdirection' => $sortDirection,
        ];
        if (isset($id)) {
            if ($result->rowCount() === 0) {
                $genre = null;
            } else {
                $genreData = $result->fetch();
                $genre = $this->createModelFromDatabaseData($genreData);
            }
            $object['body'] = $genre;
            return $object;
        } else {
            $genresData = $result->fetchAll();
            foreach ($genresData as $genreData) {
                $newGenre = $this->createModelFromDatabaseData($genreData);
                $genres[] = $newGenre;
            }
            $genres = isset($genres) ? $genres : [];
            $object['body'] = $genres;
            return $object;
        }
    }

    /**
     * @param array $genreData
     * @return array
     * @throws \Exception
     */
    public function insert($genreData)
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
        return $this->select($id);
    }

    /**
     * @param $id
     * @param $genreData
     * @return array
     * @throws \Exception
     */
    public function update($id, $genreData)
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
        return $this->select($id);
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