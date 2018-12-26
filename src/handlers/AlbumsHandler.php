<?php

namespace Handlers;

use Models\Album;
use Models\Artist;
use Models\Format;
use Models\Label;
use Models\Genre;

class AlbumsHandler extends Database
{

    /*
     * add (static?) list of fields from database
     * move other tables to own handler, remove joins
     */

    /**
     * @param $albumData
     * @return Album | null
     * @throws \Exception
     */
    public function insertAlbum($albumData)
    {
        if ($this->validatePostData($albumData)) {
            $keyValuePairs = $this->formatPostData($albumData, false);
            $query = 'INSERT' . ' INTO album (' . $keyValuePairs['keys'] . ')';
            $query .= ' VALUES (' . $keyValuePairs['values'] . ')';
            try {
                $this->db->query($query);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(), 500);
            };
            $albumId = $this->getLastInsertedAlbumId();
            return $this->getAlbum($albumId);
        } else {
            throw new \Exception('ERROR: Posted data is not valid. Album is not saved.', 500);
        }
    }

    /**
     * @param int $albumId
     * @return Album
     */
    public function getAlbum($albumId)
    {
        $query = 'SELECT ' . $this->getSelectFields() . ' FROM album';
        $query .= $this->getJoins();
        $query .= ' WHERE album.id = ' . $albumId;
        $result = $this->db->query($query);
        $albumData = $result->fetch();
        return $this->createModelsFromDatabaseData($albumData);
    }

    /**
     * @return array
     */
    public function getAlbums()
    {
        $query = 'SELECT ' . $this->getSelectFields() . ' FROM album';
        $query .= $this->getJoins();
        $result = $this->db->query($query);
        $albumsData = $result->fetchAll();
        foreach ($albumsData as $albumData) {
            $newAlbum = $this->createModelsFromDatabaseData($albumData);
            $albums[] = $newAlbum;
        }
        return isset($albums) ? $albums : [];
    }

    /**
     * @param $albumData
     * @return Album
     */
    private function createModelsFromDatabaseData($albumData)
    {
        $newAlbum = new Album([
            'id' => $albumData['album_id'],
            'title' => $albumData['album_title'],
            'year' => $albumData['album_year'],
            'date' => $albumData['album_date'],
            'notes' => $albumData['album_notes'],
        ]);
        $newAlbum->setArtist(new Artist([
            'id' => $albumData['artist_id'],
            'name' => $albumData['artist_name'],
        ]));
        $newAlbum->setGenre(new Genre([
            'id' => $albumData['genre_id'],
            'description' => $albumData['genre_description'],
            'notes' => $albumData['genre_notes'],
        ]));
        $newAlbum->setLabel(new Label([
            'id' => $albumData['label_id'],
            'name' => $albumData['label_name'],
        ]));
        $newAlbum->setFormat(new Format([
            'name' => $albumData['format_name'],
            'description' => $albumData['format_description'],
        ]));
        return $newAlbum;
    }

    /**
     * @return string
     */
    private function getJoins()
    {
        $joins = ' JOIN artist ON artist.id = album.artist_id';
        $joins .= ' JOIN genre ON genre.id = album.genre_id';
        $joins .= ' JOIN label ON label.id = album.label_id';
        $joins .= ' JOIN format ON format.id = album.format_id';
        return $joins;
    }

    /**
     * @param string $table
     * @return string
     */
    private function getSelectFields($table = null, $excludeId = false)
    {
        $selectFieldsArray = [];
        $selectFields = [
            'album' => ['id', 'title', 'year', 'date', 'notes', 'artist_id', 'genre_id', 'label_id'],
            'artist' => ['id', 'name'],
            'label' => ['id', 'name'],
            'genre' => ['id', 'description', 'notes'],
            'format' => ['id', 'name', 'description']
        ];
        if (isset($table)) {
            if (array_key_exists($table, $selectFields)) {
                foreach ($selectFields[$table] as $field) {
                    $selectFieldsArray[] = $table . '.' . $field . ' as ' . $table . '_' . $field;
                }
            }
        } else {
            foreach ($selectFields as $table => $fields) {
                foreach ($fields as $field) {
                    $selectFieldsArray[] = $table . '.' . $field . ' as ' . $table . '_' . $field;
                }
            }
        }
        return implode($selectFieldsArray, ',');
    }

    /**
     * @param $albumData
     * @param bool $excludeId
     * @return bool | array
     */
    private function formatPostData($albumData, $includeId = true)
    {
        foreach ($albumData as $key => $value) {
            if ($key !== 'id' || $includeId) {
                $keys[] = $key;
                $values[] = $value;
            }
        }
        $keyValuePairs['keys'] = implode($keys, ',');
        $keyValuePairs['values'] = '"' . implode($values, '","') . '"';
        return $keyValuePairs;
    }

    /**
     * @param array $keyvaluePairs
     * @return bool
     */
    private function validatePostData($albumData)
    {
        if (!array_key_exists('title', $albumData)) {
            return false;
        }
        return true;
    }

    /**
     * @return int
     */
    private function getLastInsertedAlbumId()
    {
        $query = 'SELECT ' . $this->getSelectFields('album') . ' FROM album ORDER BY id DESC LIMIT 1';
        $result = $this->db->query($query)->fetch();
        if (empty($result) || !array_key_exists('album_id', $result)) {
            return -1;
        }
        return $result['album_id'];
    }

}