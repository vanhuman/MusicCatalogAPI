<?php

namespace Handlers;

use Models\Album;

class AlbumsHandler extends DatabaseHandler
{
    public const RELATED_SORT_FIELDS = ['artist_name', 'label_name', 'genre_description', 'format_name'];
    private const FIELDS = ['id', 'title', 'year', 'date_added', 'notes', 'artist_id', 'genre_id', 'label_id', 'format_id'];
    private const SORT_FIELDS = ['id', 'title', 'year', 'date_added'];
    private const DEFAULT_SORT_FIELD = 'year';
    private const DEFAULT_SORT_DIRECTION = 'DESC';
    private const DEFAULT_RELATED_SORT_DIRECTION = 'ASC';

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
     * @param array $params
     * @return Album | Album[]
     * @throws \Exception
     */
    public function get($params)
    {
        $id = array_key_exists('id', $params) ? $params['id'] : null;
        if (!array_key_exists('sortBy', $params) || !in_array($params['sortBy'], self::SORT_FIELDS)) {
            $sortBy = self::DEFAULT_SORT_FIELD;
        } else {
            $sortBy = $params['sortBy'];
        }
        if (!array_key_exists('sortDirection', $params) || !in_array($params['sortDirection'], self::SORT_DIRECTION)) {
            $sortDirection = self::DEFAULT_SORT_DIRECTION;
        } else {
            $sortDirection = $params['sortDirection'];
        }
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM album';
        if (isset($id)) {
            $query .= ' WHERE id = ' . $id;
        } else {
            $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        }
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        if (isset($id)) {
            if ($result->rowCount() === 0) {
                throw new \Exception('ERROR: Album with id ' . $id . ' not found.', 500);
            }
            $albumData = $result->fetch();
            return $this->createModelFromDatabaseData($albumData);
        } else {
            $albumsData = $result->fetchAll();
            foreach ($albumsData as $albumData) {
                $newAlbum = $this->createModelFromDatabaseData($albumData);
                $albums[] = $newAlbum;
            }
            return isset($albums) ? $albums : [];
        }

    }

    /**
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getAlbumsSortedOnRelatedTable($params)
    {
        if (!array_key_exists('sortBy', $params) || !in_array($params['sortBy'], self::RELATED_SORT_FIELDS)) {
            $sortBy = 'id';
        } else {
            $sortBy = $params['sortBy'];
        }
        if (!array_key_exists('sortDirection', $params) || !in_array($params['sortDirection'], self::SORT_DIRECTION)) {
            $sortDirection = self::DEFAULT_RELATED_SORT_DIRECTION;
        } else {
            $sortDirection = $params['sortDirection'];
        }
        // sortBy is always formatted as table_field
        $relatedTable = explode('_', $sortBy)[0];
        $sortField = str_replace('_', '.', $sortBy);
        $query = 'SELECT * FROM album';
        $query .= ' JOIN ' . $relatedTable . ' ON ' . $relatedTable . '.id = album.' . $relatedTable . '_id';
        $query .= ' ORDER BY ' . $sortField . ' ' . $sortDirection;
        std()->show($query, 'Database query');
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
        $query = 'INSERT INTO album (' . $postData['keys'] . ')';
        $query .= ' VALUES (' . $postData['values'] . ')';
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        try {
            $id = $this->getLastInsertedRecordId('album');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        return $this->get($id);
    }

    /**
     * @param int $id
     * @param $albumData
     * @return Album
     * @throws \Exception
     */
    public function updateAlbum($id, $albumData)
    {
        try {
            $this->validatePostData($albumData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        $postData = $this->formatPostdataForUpdate($albumData);
        $query = 'UPDATE album SET ' . $postData . ' WHERE id = ' . $id;
        try {
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        return $this->get($id);
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
                $newAlbum->setArtist($this->artistsHandler->get(['id' => $albumData['artist_id']]));
            } catch (\Exception $e) {
            }
        }
        if (array_key_exists('genre_id', $albumData)) {
            try {
                $newAlbum->setGenre($this->genresHandler->get(['id' => $albumData['genre_id']]));
            } catch (\Exception $e) {
            }
        }
        if (array_key_exists('label_id', $albumData)) {
            try {
                $newAlbum->setLabel($this->labelsHandler->get(['id' => $albumData['label_id']]));
            } catch (\Exception $e) {
            }
        }
        if (array_key_exists('format_id', $albumData)) {
            try {
                $newAlbum->setFormat($this->formatsHandler->get(['id' => $albumData['format_id']]));
            } catch (\Exception $e) {
            }
        }
        return $newAlbum;
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
            $artist = $this->artistsHandler->get($albumData['artist_id']);
            $label = $this->labelsHandler->get($albumData['label_id']);
            $genre = $this->genresHandler->get($albumData['genre_id']);
            $format = $this->formatsHandler->get($albumData['format_id']);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 400);
        }
        foreach (['artist' => $artist, 'label' => $label, 'genre' => $genre, 'format' => $format] as $key => $entity) {
            if (!isset($entity)) {
                throw new \Exception(ucfirst($key) . ' with id ' . $albumData[$key . '_id'] . ' cannot be found.', 400);
            }
        }
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