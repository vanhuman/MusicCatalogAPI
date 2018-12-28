<?php

namespace Handlers;

use Models\Album;

//use Models\Artist;
//use Models\Format;
//use Models\Label;
//use Models\Genre;

class AlbumsHandler extends Database
{
    public const FIELDS = ['id', 'title', 'year', 'date_added', 'notes', 'artist_id', 'genre_id', 'label_id', 'format_id'];

    /**
     * @var ArtistsHandler $artistsHandler
     */
    private $artistsHandler;

    /**
     * @var GenresHandler $genresHandler
     */
    private $genresHandler;

    /**
     * @var LabelsHandler $labelsHandler
     */
    private $labelsHandler;

    /**
     * @var FormatsHandler $formatsHandler
     */
    private $formatsHandler;

    /**
     * AlbumsHandler constructor.
     * @param $db
     */
    public function __construct($db)
    {
        parent::__construct($db);
        $this->artistsHandler = new ArtistsHandler($db);
        $this->genresHandler = new GenresHandler($db);
        $this->labelsHandler = new LabelsHandler($db);
        $this->formatsHandler = new FormatsHandler($db);
    }

    /**
     * @param int $albumId
     * @return boolean
     * @throws \Exception
     */
    public function deleteAlbum($albumId)
    {
        $query = 'DELETE' . ' album WHERE id = ' . $albumId;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        return $result;
    }

    /**
     * @param int $albumId
     * @param $albumData
     * @return Album | null
     * @throws \Exception
     */
    public function updateAlbum($albumId, $albumData)
    {
        try {
            $this->validatePostData($albumData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForUpdate($albumData);
        $query = 'UPDATE' . ' album SET ' . $postData . ' WHERE id = ' . $albumId;
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        return $this->getAlbum($albumId);
    }

    /**
     * @param $albumData
     * @return Album | null
     * @throws \Exception
     */
    public function insertAlbum($albumData)
    {
        try {
            $this->validatePostData($albumData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForInsert($albumData);
        $query = 'INSERT' . ' INTO album (' . $postData['keys'] . ')';
        $query .= ' VALUES (' . $postData['values'] . ')';
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        $albumId = $this->getLastInsertedAlbumId();
        return $this->getAlbum($albumId);
    }

    /**
     * @param int $albumId
     * @return Album
     * @throws \Exception
     */
    public function getAlbum($albumId)
    {
        std()->show($albumId);
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM album WHERE id = ' . $albumId;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $albumData = $result->fetch();
        return $this->createModelFromDatabaseData($albumData);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getAlbums()
    {
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM album';
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $albumsData = $result->fetchAll();
        foreach ($albumsData as $albumData) {
            $newAlbum = $this->createModelFromDatabaseData($albumData);
            $albums[] = $newAlbum;
        }
        return isset($albums) ? $albums : [];
    }

    /**
     * @param $albumData
     * @return Album
     */
    private function createModelFromDatabaseData($albumData)
    {
        $newAlbum = new Album([
            'id' => $albumData['id'],
            'title' => $albumData['title'],
            'year' => $albumData['year'],
            'dateAdded' => $albumData['date_added'],
            'notes' => $albumData['notes'],
        ]);
        if (array_key_exists('artist_id', $albumData)) {
            try {
                $newAlbum->setArtist($this->artistsHandler->getArtist($albumData['artist_id']));
            } catch (\Exception $e) {
            }
        }
        if (array_key_exists('genre_id', $albumData)) {
            try {
                $newAlbum->setGenre($this->genresHandler->getGenre($albumData['genre_id']));
            } catch (\Exception $e) {
            }
        }
        try {
            if (array_key_exists('label_id', $albumData)) {
                $newAlbum->setLabel($this->labelsHandler->getLabel($albumData['label_id']));
            }
        } catch (\Exception $e) {
        }
        try {
            if (array_key_exists('format_id', $albumData)) {
                $newAlbum->setFormat($this->formatsHandler->getFormat($albumData['format_id']));
            }
        } catch (\Exception $e) {
        }
        return $newAlbum;
    }

    /**
     * @param $albumData
     * @param bool $excludeId
     * @return bool | array
     */
    private function formatPostdataForInsert($albumData)
    {
        foreach ($albumData as $key => $value) {
            if ($key !== 'id') {
                $keys[] = $key;
                $values[] = $value;
            }
        }
        $postData['keys'] = implode($keys, ',');
        $postData['values'] = '"' . implode($values, '","') . '"';
        return $postData;
    }

    /**
     * @param $albumData
     * @param bool $excludeId
     * @return bool | array
     */
    private function formatPostdataForUpdate($albumData)
    {
        foreach ($albumData as $key => $value) {
            if ($key !== 'id') {
                $postData[] = $key . ' = "' . $value . '"';
            }
        }
        $postData = implode(',', $postData);
        return $postData;
    }

    /**
     * @param array $albumData
     * @throws \Exception
     */
    private function validatePostData($albumData)
    {
        // title is mandatory
        if (!array_key_exists('title', $albumData)) {
            throw new \Exception('Title is a mandatory field.', 400);
        }
        // artist_id is mandatory
        if (!array_key_exists('artist_id', $albumData)) {
            throw new \Exception('Artist_id is a mandatory field.', 400);
        }
        // year should be 4 digits
        if (array_key_exists('year', $albumData)) {
            $year = $albumData['year'];
            if (!is_numeric($year) || (int)$year < 1900 || (int)$year > 4000) {
                throw new \Exception('Year should be a 4 digit number between 1900 and 4000.', 400);
            }
        }
        // other keys than the database fields are not allowed
        foreach ($albumData as $key => $value) {
            if (!in_array($key, self::FIELDS)) {
                throw new \Exception($key . ' is not a valid field for this endpoint.', 400);
            }
        }
        // check existence of artist, genre, label and format
        try {
            $artist = $this->artistsHandler->getArtist($albumData['artist_id']);
            $label = $this->labelsHandler->getLabel($albumData['label_id']);
            $genre = $this->genresHandler->getGenre($albumData['genre_id']);
            $format = $this->formatsHandler->getFormat($albumData['format_id']);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        foreach (['artist' => $artist, 'label' => $label, 'genre' => $genre, 'format' => $format] as $key => $entity) {
            if (!isset($entity)) {
                throw new \Exception(ucfirst($key) . ' with id ' . $albumData[$key . '_id'] . ' cannot be found.', 400);
            }
        }
    }

    /**
     * @return int
     */
    private function getLastInsertedAlbumId()
    {
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM album ORDER BY id DESC LIMIT 1';
        $result = $this->db->query($query)->fetch();
        if (empty($result) || !array_key_exists('id', $result)) {
            return -1;
        }
        return $result['id'];
    }

///// Select with joins instead of getting all related records as separate select statements ////////////////////
//    /**
//     * @return array
//     */
//    public function getAlbumsOptimized()
//    {
//        $query = 'SELECT ' . $this->getSelectFields() . ' FROM album';
//        $query .= $this->getJoins();
//        $result = $this->db->query($query);
//        $albumsData = $result->fetchAll();
//        foreach ($albumsData as $albumData) {
//            $newAlbum = $this->createModelsFromDatabaseData($albumData);
//            $albums[] = $newAlbum;
//        }
//        return isset($albums) ? $albums : [];
//    }
//
//    /**
//     * @param $albumData
//     * @return Album
//     */
//    private function createModelsFromDatabaseData($albumData)
//    {
//        $newAlbum = new Album([
//            'id' => $albumData['album_id'],
//            'title' => $albumData['album_title'],
//            'year' => $albumData['album_year'],
//            'date' => $albumData['album_date'],
//            'notes' => $albumData['album_notes'],
//        ]);
//        $newAlbum->setArtist(new Artist([
//            'id' => $albumData['artist_id'],
//            'name' => $albumData['artist_name'],
//        ]));
//        $newAlbum->setGenre(new Genre([
//            'id' => $albumData['genre_id'],
//            'description' => $albumData['genre_description'],
//            'notes' => $albumData['genre_notes'],
//        ]));
//        $newAlbum->setLabel(new Label([
//            'id' => $albumData['label_id'],
//            'name' => $albumData['label_name'],
//        ]));
//        $newAlbum->setFormat(new Format([
//            'name' => $albumData['format_name'],
//            'description' => $albumData['format_description'],
//        ]));
//        return $newAlbum;
//    }
//
//    /**
//     * @return string
//     */
//    private function getJoins()
//    {
//        $joins = ' JOIN artist ON artist.id = album.artist_id';
//        $joins .= ' JOIN genre ON genre.id = album.genre_id';
//        $joins .= ' JOIN label ON label.id = album.label_id';
//        $joins .= ' JOIN format ON format.id = album.format_id';
//        return $joins;
//    }
//
//    /**
//     * @param string $table
//     * @return string
//     */
//    private function getSelectFields($table = null, $excludeId = false)
//    {
//        $selectFieldsArray = [];
//        $selectFields = [
//            'album' => ['id', 'title', 'year', 'date', 'notes', 'artist_id', 'genre_id', 'label_id'],
//            'artist' => ['id', 'name'],
//            'label' => ['id', 'name'],
//            'genre' => ['id', 'description', 'notes'],
//            'format' => ['id', 'name', 'description']
//        ];
//        if (isset($table)) {
//            if (array_key_exists($table, $selectFields)) {
//                foreach ($selectFields[$table] as $field) {
//                    $selectFieldsArray[] = $table . '.' . $field . ' as ' . $table . '_' . $field;
//                }
//            }
//        } else {
//            foreach ($selectFields as $table => $fields) {
//                foreach ($fields as $field) {
//                    $selectFieldsArray[] = $table . '.' . $field . ' as ' . $table . '_' . $field;
//                }
//            }
//        }
//        return implode($selectFieldsArray, ',');
//    }

}