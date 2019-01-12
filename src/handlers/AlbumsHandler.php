<?php

namespace Handlers;

use Models\Album;
use Helpers\TypeUtility;
use Models\Params;

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
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function selectById($id)
    {
        if (!isset($id) || !TypeUtility::isInteger($id)) {
            $id = 0;
        }
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM album WHERE id = ' . $id;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $object = [
            'query' => $query,
        ];
        if ($result->rowCount() === 0) {
            $album = null;
        } else {
            $albumData = $result->fetch();
            $album = $this->createModelFromDatabaseData($albumData);
        }
        $object['body'] = $album;
        return $object;
    }

    /**
     * @param Params $params
     * @return array
     * @throws \Exception
     */
    public function select(Params $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::SORT_FIELDS, self::DEFAULT_SORT_FIELD);
        $sortDirection = $this->getSortDirectionFromParams($params, self::DEFAULT_SORT_DIRECTION);
        $page = $params->page;
        $pageSize = $params->pageSize;
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM album';
        $query .= ' WHERE true';
        $query .= $this->getFilterClause($params);
        $query .= ' ORDER BY ' . $sortBy . ' ' . $sortDirection;
        $queryWithoutLimit = $query;
        $query .= ' LIMIT ' . ($pageSize * ($page - 1)) . ',' . $pageSize;
        try {
            $result = $this->db->query($query);
            $resultWithoutLimit = $this->db->query($queryWithoutLimit);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $object = [
            'total_number_of_records' => $resultWithoutLimit->rowCount(),
            'query' => $query,
            'sortby' => $sortBy,
            'sortdirection' => $sortDirection,
        ];
        $albumsData = $result->fetchAll();
        foreach ($albumsData as $albumData) {
            $newAlbum = $this->createModelFromDatabaseData($albumData);
            $albums[] = $newAlbum;
        }
        $albums = isset($albums) ? $albums : [];
        $object['body'] = $albums;
        return $object;
    }

    /**
     * @param Params $params
     * @return array
     * @throws \Exception
     */
    public function getAlbumsSortedOnRelatedTable(Params $params)
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
        $page = $params->page;
        $pageSize = $params->pageSize;
        $query = 'SELECT ' . $selectFields . ' FROM album';
        $query .= ' JOIN ' . $relatedTable . ' ON ' . $relatedTable . '.id = album.' . $relatedTable . '_id';
        $query .= ' WHERE true';
        $query .= $this->getFilterClause($params);
        $query .= ' ORDER BY ' . $sortField . ' ' . $sortDirection;
        $queryWithoutLimit = $query;
        $query .= ' LIMIT ' . ($pageSize * ($page - 1)) . ',' . $pageSize;
        try {
            $result = $this->db->query($query);
            $resultWithoutLimit = $this->db->query($queryWithoutLimit);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $object = [
            'total_number_of_records' => $resultWithoutLimit->rowCount(),
            'query' => $query,
            'sortby' => $sortBy,
            'sortdirection' => $sortDirection,
        ];
        $albumsData = $result->fetchAll();
        foreach ($albumsData as $albumData) {
            $newAlbum = $this->createModelFromDatabaseData($albumData);
            $albums[] = $newAlbum;
        }
        $albums = isset($albums) ? $albums : [];
        $object['body'] = $albums;
        return $object;
    }

    /**
     * @param array $albumData
     * @return array | null
     * @throws \Exception
     */
    public function insert($albumData)
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
        return $this->selectById($id);
    }

    /**
     * @param int $id
     * @param $albumData
     * @return array
     * @throws \Exception
     */
    public function update($id, $albumData)
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
        return $this->selectById($id);
    }

    /**
     * Build part of WHERE clause depending on filter params.
     * @param Params $params
     * @return string
     */
    private function getFilterClause(Params $params)
    {
        $filterClause = '';
        $filter = $params->filter;
        if (isset($filter->artist_id) && TypeUtility::isInteger($filter->artist_id)) {
            $filterClause .= ' AND artist_id = ' . $filter->artist_id;
        }
        if (isset($filter->label_id) && TypeUtility::isInteger($filter->label_id)) {
            $filterClause .= ' AND label_id = ' . $filter->label_id;
        }
        if (isset($filter->genre_id) && TypeUtility::isInteger($filter->genre_id)) {
            $filterClause .= ' AND genre_id = ' . $filter->genre_id;
        }
        if (isset($filter->format_id) && TypeUtility::isInteger($filter->format_id)) {
            $filterClause .= ' AND format_id = ' . $filter->format_id;
        }
        return $filterClause;
    }

    /**
     * Create the necessary models from the database data to return the full Album model.
     * @param array $albumData
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
                $artist = $this->artistsHandler->selectById($albumData['artist_id'])['body'];
                $newAlbum->setArtist($artist);
            } catch (\Exception $e) {
            }
        }
        if (array_key_exists('genre_id', $albumData)) {
            try {
                $genre = $this->genresHandler->selectById($albumData['genre_id'])['body'];
                $newAlbum->setGenre($genre);
            } catch (\Exception $e) {
            }
        }
        if (array_key_exists('label_id', $albumData)) {
            try {
                $label = $this->labelsHandler->selectById($albumData['label_id'])['body'];
                $newAlbum->setLabel($label);
            } catch (\Exception $e) {
            }
        }
        if (array_key_exists('format_id', $albumData)) {
            try {
                $format = $this->formatsHandler->selectById($albumData['format_id'])['body'];
                $newAlbum->setFormat($format);
            } catch (\Exception $e) {
            }
        }
        return $newAlbum;
    }

    /**
     * Post data validation specific for albums.
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
            $artist = $this->artistsHandler->selectById($postData['artist_id']);
            $format = $this->formatsHandler->selectById($postData['format_id']);
            if (array_key_exists('label_id', $postData)) {
                $label = $this->labelsHandler->selectById($postData['label_id']);
            } else {
                $label = false;
            }
            if (array_key_exists('genre_id', $postData)) {
                $genre = $this->genresHandler->selectById($postData['genre_id']);
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
}