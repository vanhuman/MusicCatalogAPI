<?php

namespace Handlers;

use Models\Artist;
use Helpers\TypeUtility;
use Models\GetParams;

class ArtistsHandler extends DatabaseHandler
{
    private static $FIELDS = [
        'fields' => ['id', 'name'],
        'mandatoryFields' => ['name'],
        'sortFields' => ['id', 'name'],
        'sortDirections' => parent::SORT_DIRECTIONS,
        'defaultSortField' => 'name',
        'defaultSortDirection' => 'ASC',
    ];

    /**
     * @throws \Exception
     * @return array
     */
    public function selectById(int $id)
    {
        if (!isset($id) || !TypeUtility::isInteger($id)) {
            $id = 0;
        }
        $query = 'SELECT ' . implode(self::$FIELDS['fields'], ',') . ' FROM artist';
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
     * @throws \Exception
     * @return array
     */
    public function select(GetParams $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::$FIELDS['sortFields'], self::$FIELDS['defaultSortField']);
        $sortDirection = $this->getSortDirectionFromParams($params, self::$FIELDS['defaultSortDirection']);
        $page = $params->page;
        $pageSize = $params->pageSize;

        $query = 'SELECT ' . implode(self::$FIELDS['fields'], ',') . ' FROM artist';
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
     * @throws \Exception
     */
    private function validatePostData(array $postData)
    {
        $this->validateMandatoryFields($postData, self::$FIELDS['mandatoryFields']);
        $this->validateKeys($postData, self::$FIELDS['fields']);
    }
}