<?php

namespace Handlers;

use Models\Artist;
use Helpers\TypeUtility;
use Models\GetParams;

class ArtistsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'name'];
    private const MANDATORY_FIELDS = ['name'];

    private const SORT_FIELDS = ['id', 'name'];
    private const DEFAULT_SORT_FIELD = 'name';
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
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM artist';
        $query .= ' WHERE id = ' . $id;
        $result = $this->db->query($query);
        $object = [
            'query' => $query,
        ];
        if ($result->rowCount() === 0) {
            $artist = null;
        } else {
            $artistData = $result->fetch();
            $artist = $this->createModelFromDatabaseData($artistData);
        }
        $object['body'] = $artist;
        return $object;
    }

    /**
     * @param GetParams $params
     * @throws \Exception
     * @return array
     */
    public function select(GetParams $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $page = $params->page;
        $pageSize = $params->pageSize;

        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM artist';
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
        $artistsData = $result->fetchAll();
        foreach ($artistsData as $artistData) {
            $newArtist = $this->createModelFromDatabaseData($artistData);
            $artists[] = $newArtist;
        }
        $artists = isset($artists) ? $artists : [];
        $object['body'] = $artists;
        return $object;
    }

    /**
     * @param array $artistData
     * @return array
     * @throws \Exception
     */
    public function insert(array $artistData)
    {
        $this->validatePostData($artistData);
        $postData = $this->formatPostdataForInsert($artistData);
        $query = 'INSERT INTO artist (' . $postData['keys'] . ')';
        $query .= ' VALUES (' . $postData['values'] . ')';
        $this->db->query($query);
        $id = $this->db->lastInsertId();
        return $this->selectById($id);
    }

    /**
     * @param int $id
     * @param array $artistData
     * @return array | null
     * @throws \Exception
     */
    public function update(int $id, array $artistData)
    {
        $this->validatePostData($artistData);
        $query = 'SELECT id FROM artist WHERE id = ' . $id;
        if ($this->db->query($query)->rowCount() === 0) {
            return null;
        }
        $postData = $this->formatPostdataForUpdate($artistData);
        $query = 'UPDATE artist SET ' . $postData . ' WHERE id = ' . $id;
        $this->db->query($query);
        return $this->selectById($id);
    }

    /**
     * @param array $artistData
     * @return Artist
     */
    private function createModelFromDatabaseData(array $artistData)
    {
        $newArtist = new Artist([
            'id' => $artistData['id'],
            'name' => $artistData['name'],
        ]);
        return $newArtist;
    }

    /**
     * @param array $postData
     * @throws \Exception
     */
    private function validatePostData(array $postData)
    {
        $this->validateMandatoryFields($postData, self::MANDATORY_FIELDS);
        $this->validateKeys($postData, self::FIELDS);
    }
}