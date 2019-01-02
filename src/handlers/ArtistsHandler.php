<?php

namespace Handlers;

use Models\Artist;

class ArtistsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'name'];
    private const SORT_FIELDS = ['id', 'name'];
    private const DEFAULT_SORT_FIELD = 'name';
    private const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
     * @param array | int $params
     * @throws \Exception
     * @return Artist | Artist[]
     */
    public function get($params)
    {
        $id = $this->getIdFromParams($params);
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM artist';
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
            $artistData = $result->fetch();
            if ($result->rowCount() === 0) {
                throw new \Exception('ERROR: Artist with id ' . $id . ' not found.', 500);
            }
            return $this->createModelFromDatabaseData($artistData);
        } else {
            $artistsData = $result->fetchAll();
            foreach ($artistsData as $artistData) {
                $newArtist = $this->createModelFromDatabaseData($artistData);
                $artists[] = $newArtist;
            }
            return isset($artists) ? $artists : [];
        }
    }

    /**
     * @param $artistData
     * @return Artist
     * @throws \Exception
     */
    public function insertArtist($artistData)
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
        return $this->get(['id' => $id]);
    }

    /**
     * @param $id
     * @param $artistData
     * @return Artist
     * @throws \Exception
     */
    public function updateArtist($id, $artistData)
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
        return $this->get($id);
    }

    /**
     * @param $artistData
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
     * @param $artistData
     * @throws \Exception
     */
    private function validatePostData($artistData)
    {
        // name is mandatory
        if (!array_key_exists('name', $artistData)) {
            throw new \Exception('Name is a mandatory field.', 400);
        }
        // other keys than the database fields are not allowed
        foreach ($artistData as $key => $value) {
            if (!in_array($key, self::FIELDS)) {
                throw new \Exception($key . ' is not a valid field for this endpoint.', 400);
            }
        }
    }
}