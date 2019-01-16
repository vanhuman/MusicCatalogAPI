<?php

namespace Handlers;

use Helpers\TypeUtility;
use Models\Genre;
use Models\GetParams;

class GenresHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'description', 'notes'];
    private const MANDATORY_FIELDS = ['description'];

    private const SORT_FIELDS = ['id', 'description'];
    private const DEFAULT_SORT_FIELD = 'id';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
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
        $result = $this->db->query($query);
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
     * @throws \Exception
     * @return array
     */
    public function select(GetParams $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $page = $params->page;
        $pageSize = $params->pageSize;

        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM genre';
        $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        $queryWithoutLimit = $query;
        $query .= ' LIMIT ' . ($pageSize * ($page - 1)) . ',' . $pageSize;

        $result = $this->db->query($query);
        $resultWithoutLimit = $this->db->query($queryWithoutLimit);
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
     * @return array
     * @throws \Exception
     */
    public function insert(array $genreData)
    {
        $this->validatePostData($genreData);
        $postData = $this->formatPostdataForInsert($genreData);
        $query = 'INSERT INTO genre (' . $postData['keys'] . ')';
        $query .= ' VALUES (' . $postData['values'] . ')';
        $this->db->query($query);
        $id = $this->db->lastInsertId();
        return $this->selectById($id);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function update(int $id, array $genreData)
    {
        $this->validatePostData($genreData);
        $query = 'SELECT id FROM genre WHERE id = ' . $id;
        if ($this->db->query($query)->rowCount() === 0) {
            return null;
        }
        $postData = $this->formatPostdataForUpdate($genreData);
        $query = 'UPDATE genre SET ' . $postData . ' WHERE id = ' . $id;
        $this->db->query($query);
        return $this->selectById($id);
    }

    /**
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
     * @throws \Exception
     */
    private function validatePostData(array $postData)
    {
        $this->validateMandatoryFields($postData, self::MANDATORY_FIELDS);
        $this->validateKeys($postData, self::FIELDS);
    }

}