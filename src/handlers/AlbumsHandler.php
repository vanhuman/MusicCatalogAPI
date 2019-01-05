<?php

namespace Handlers;

use Models\Album;
use Helpers\TypeUtility;

class AlbumsHandler extends DatabaseHandler
{
    private const FIELDS = ['id', 'title', 'year', 'date_added', 'notes', 'artist_id', 'genre_id', 'label_id', 'format_id'];
    private const MANDATORY_FIELDS = ['title', 'artist_id', 'format_id'];

    private const SORT_FIELDS = ['id', 'title', 'year', 'date_added'];
    private const DEFAULT_SORT_FIELD = 'year';
    private const DEFAULT_SORT_DIRECTION = 'DESC';
    private const DEFAULT_RELATED_SORT_DIRECTION = 'ASC';

    // these fields should be in the format table_field
    public const RELATED_SORT_FIELDS = ['artist_name', 'label_name', 'genre_description', 'format_name'];

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
     * @param array | int $params
     * @return Album | Album[]
     * @throws \Exception
     */
    public function get($params)
    {
        $id = $this->getIdFromParams($params);
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $page = array_key_exists('page', $params) ? $params['page'] : 1;
        $pageSize = array_key_exists('page_size', $params) ? $params['page_size'] : 50;
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM album';
        $query .= ' WHERE true';
        if (isset($id)) {
            $query .= ' AND id = ' . $id;
        }
        $query .= $this->getFilterClause($params);
        $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        $query .= ' LIMIT ' . ($pageSize * ($page - 1))  . ',' . $pageSize;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        if (isset($id)) {
            if ($result->rowCount() === 0) {
                throw new \Exception('ERROR: Album with specified id and optional filter settings not found.', 500);
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
        $sortBy = $this->getSortByFromParams($params, self::RELATED_SORT_FIELDS, 'id');
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_RELATED_SORT_DIRECTION);
        // sortBy is always formatted as table_field
        $relatedTable = explode('_', $sortBy)[0];
        $sortField = str_replace('_', '.', $sortBy);
        $selectFunc = function ($field) {
          return 'album.' . $field;
        };
        $selectFields = implode(array_map($selectFunc, self::FIELDS), ',');
        $page = array_key_exists('page', $params) ? $params['page'] : 1;
        $pageSize = array_key_exists('page_size', $params) ? $params['page_size'] : 50;
        $query = 'SELECT ' . $selectFields . ' FROM album';
        $query .= ' JOIN ' . $relatedTable . ' ON ' . $relatedTable . '.id = album.' . $relatedTable . '_id';
        $query .= ' WHERE true';
        if (isset($id)) {
            $query .= ' AND id = ' . $id;
        }
        $query .= $this->getFilterClause($params);
        $query .= ' ORDER BY ' . $sortField . ' ' . $sortDirection;
        $query .= ' LIMIT ' . ($pageSize * ($page - 1))  . ',' . $pageSize;
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

    private function getFilterClause($params)
    {
        $filterClause = '';
        if (array_key_exists('filter', $params)) {
            $filter = $params['filter'];
            $artist_id = array_key_exists('artist_id', $filter) ? $filter['artist_id'] : null;
            $label_id = array_key_exists('label_id', $filter) ? $filter['label_id'] : null;
            $genre_id = array_key_exists('genre_id', $filter) ? $filter['genre_id'] : null;
            $format_id = array_key_exists('format_id', $filter) ? $filter['format_id'] : null;
            if (isset($artist_id) && TypeUtility::isInteger($artist_id)) {
                $filterClause .= ' AND artist_id = ' . $artist_id;
            }
            if (isset($label_id) && TypeUtility::isInteger($label_id)) {
                $filterClause .= ' AND label_id = ' . $label_id;
            }
            if (isset($genre_id) && TypeUtility::isInteger($genre_id)) {
                $filterClause .= ' AND genre_id = ' . $genre_id;
            }
            if (isset($format_id) && TypeUtility::isInteger($format_id)) {
                $filterClause .= ' AND format_id = ' . $format_id;
            }
        }
        return $filterClause;
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
                $newAlbum->setArtist($this->artistsHandler->get($albumData['artist_id']));
            } catch (\Exception $e) {
            }
        }
        if (array_key_exists('genre_id', $albumData)) {
            try {
                $newAlbum->setGenre($this->genresHandler->get($albumData['genre_id']));
            } catch (\Exception $e) {
            }
        }
        if (array_key_exists('label_id', $albumData)) {
            try {
                $newAlbum->setLabel($this->labelsHandler->get($albumData['label_id']));
            } catch (\Exception $e) {
            }
        }
        if (array_key_exists('format_id', $albumData)) {
            try {
                $newAlbum->setFormat($this->formatsHandler->get($albumData['format_id']));
            } catch (\Exception $e) {
            }
        }
        return $newAlbum;
    }

    /**
     * @param array $postData
     * @throws \Exception
     */
    private function validatePostData($postData)
    {
        // general validation
        try {
            $this->validateMandatoryFields($postData, self::MANDATORY_FIELDS);
            $this->validateKeys($postData, self::FIELDS);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        // year should be 4 digits
        if (array_key_exists('year', $postData)) {
            $year = $postData['year'];
            if (!TypeUtility::isInteger($year) || (int)$year < 1900 || (int)$year > 4000) {
                throw new \Exception('Year should be a 4 digit number between 1900 and 4000.', 400);
            }
        }
        // check existence of artist, genre, label and format
        try {
            $artist = $this->artistsHandler->get($postData['artist_id']);
            $format = $this->formatsHandler->get($postData['format_id']);
            if (array_key_exists('label_id', $postData)) {
                $label = $this->labelsHandler->get($postData['label_id']);
            } else {
                $label = false;
            }
            if (array_key_exists('genre_id', $postData)) {
                $genre = $this->genresHandler->get($postData['genre_id']);
            } else {
                $genre = false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 400);
        }
        foreach (['artist' => $artist, 'label' => $label, 'genre' => $genre, 'format' => $format] as $key => $entity) {
            if (!isset($entity)) {
                throw new \Exception(ucfirst($key) . ' with id ' . $postData[$key . '_id'] . ' cannot be found.', 400);
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