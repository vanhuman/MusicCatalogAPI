<?php

namespace Handlers;

use Models\Artist;

class ArtistsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'name'];
    private const MANDATORY_FIELDS = ['name'];

    private const SORT_FIELDS = ['id', 'name'];
    private const DEFAULT_SORT_FIELD = 'name';
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
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM artist';
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
                $artist = null;
            } else {
                $artistData = $result->fetch();
                $artist = $this->createModelFromDatabaseData($artistData);
            }
            $object['body'] = $artist;
            return $object;
        } else {
            $artistsData = $result->fetchAll();
            foreach ($artistsData as $artistData) {
                $newArtist = $this->createModelFromDatabaseData($artistData);
                $artists[] = $newArtist;
            }
            $artists = isset($artists) ? $artists : [];
            $object['body'] = $artists;
            return $object;
        }
    }

    /**
     * @param array $artistData
     * @return array
     * @throws \Exception
     */
    public function insert($artistData)
    {
        try {
            $this->validatePostData($artistData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForInsert($artistData);
        $query = 'INSERT INTO artist (' . $postData['keys'] . ')';
        $query .= ' VALUES (' . $postData['values'] . ')';
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        $id = $this->getLastInsertedRecordId('artist');
        return $this->select($id);
    }

    /**
     * @param int $id
     * @param array $artistData
     * @return array
     * @throws \Exception
     */
    public function update($id, $artistData)
    {
        try {
            $this->validatePostData($artistData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForUpdate($artistData);
        $query = 'UPDATE artist SET ' . $postData . ' WHERE id = ' . $id;
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        return $this->select($id);
    }

    /**
     * @param array $artistData
     * @return Artist
     */
    private function createModelFromDatabaseData($artistData)
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