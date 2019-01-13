<?php

namespace Handlers;

use Helpers\TypeUtility;
use Models\Genre;
use Models\Params;

class GenresHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'description', 'notes'];
    private const MANDATORY_FIELDS = ['description'];

    private const SORT_FIELDS = ['id', 'description'];
    private const DEFAULT_SORT_FIELD = 'id';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
     * @param int $id
     * @throws \Exception
     * @return array
     */
    public function selectById(int $id)
    {
        if (!isset($id) || !TypeUtility::isInteger($id)) {
            $id = 0;
        }
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM genre';
        $query .= ' WHERE id = ' . $id;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $object = [
            'query' => $query,
        ];
        if ($result->rowCount() === 0) {
            $genre = null;
        } else {
            $genreData = $result->fetch();
            $genre = $this->createModelFromDatabaseData($genreData);
        }
        $object['body'] = $genre;
        return $object;
    }

    /**
     * @param Params $params
     * @throws \Exception
     * @return array
     */
    public function select(Params $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $page = $params->page;
        $pageSize = $params->pageSize;
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM genre';
        $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        $queryWithoutLimit = $query;
        $query .= ' LIMIT ' . ($pageSize * ($page - 1)) . ',' . $pageSize;
        try {
            $result = $this->db->query($query);
            $resultWithoutLimit = $this->db->query($queryWithoutLimit);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $object = [
            'total_number_of_records' => $resultWithoutLimit->rowCount(),
            'query' => $query,
            'sortby' => $sortBy,
            'sortdirection' => $sortDirection,
        ];
        $genresData = $result->fetchAll();
        foreach ($genresData as $genreData) {
            $newGenre = $this->createModelFromDatabaseData($genreData);
            $genres[] = $newGenre;
        }
        $genres = isset($genres) ? $genres : [];
        $object['body'] = $genres;
        return $object;
    }

    /**
     * @param array $genreData
     * @return array
     * @throws \Exception
     */
    public function insert(array $genreData)
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
        return $this->selectById($id);
    }

    /**
     * @param int $id
     * @param array $genreData
     * @return array
     * @throws \Exception
     */
    public function update(int $id, array $genreData)
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
        return $this->selectById($id);
    }

    /**
     * @param array $genreData
     * @return Genre
     */
    private function createModelFromDatabaseData(array $genreData)
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
    private function validatePostData(array $postData)
    {
        try {
            $this->validateMandatoryFields($postData, self::MANDATORY_FIELDS);
            $this->validateKeys($postData, self::FIELDS);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

}