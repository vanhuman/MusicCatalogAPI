<?php

namespace Handlers;

use Models\Artist;

class ArtistsHandler extends Handler
{
    private const FIELDS = ['id', 'name'];
    private const SORT_FIELDS = ['id', 'name'];

    /**
     * @param int $artistId
     * @throws \Exception
     * @return Artist $artist | null
     */
    public function getArtist($artistId)
    {
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM artist WHERE id = ' . $artistId;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $artistData = $result->fetch();
        if ($result->rowCount() === 0) {
            throw new \Exception('ERROR: Artist with id ' . $artistId . ' not found.', 500);
        }
        return  $this->createModelFromDatabaseData($artistData);
    }

    /**
     * @param string $sortBy
     * @param string $sortDirection
     * @throws \Exception
     * @return Artist[] $artist | null
     */
    public function getArtists($sortBy = 'name', $sortDirection = 'ASC')
    {
        if (!in_array($sortBy, self::SORT_FIELDS)) {
            $sortBy = 'name';
        }
        if (!in_array($sortDirection, self::SORT_DIRECTION)) {
            $sortDirection = 'ASC';
        }
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM artist';
        $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $artistsData = $result->fetchAll();
        foreach ($artistsData as $artistData) {
            $newArtist = $this->createModelFromDatabaseData($artistData);
            $artists[] = $newArtist;
        }
        return isset($artists) ? $artists : [];
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
        $artistId = $this->getLastInsertedRecordId('artist');
        return $this->getArtist($artistId);
    }

    /**
     * @param $artistId
     * @param $artistData
     * @return Artist
     * @throws \Exception
     */
    public function updateArtist($artistId, $artistData)
    {
        try {
            $this->validatePostData($artistData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForUpdate($artistData);
        $query = 'UPDATE artist SET ' . $postData . ' WHERE id = ' . $artistId;
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        return $this->getArtist($artistId);
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