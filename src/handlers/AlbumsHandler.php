<?php

namespace Handlers;

use Models\Album;
use Helpers\TypeUtility;
use Models\GetParams;

class AlbumsHandler extends DatabaseHandler
{
    public static $FIELDS = [
        'fields' => ['id', 'title', 'year', 'date_added', 'notes', 'artist_id', 'genre_id', 'label_id', 'format_id'],
        'mandatoryFields' => ['title', 'artist_id', 'format_id'],
        'sortFields' => ['id', 'title', 'year', 'date_added'],
        'sortDirections' => parent::SORT_DIRECTIONS,
        'defaultSortField' => 'year',
        'defaultSortDirection' => 'DESC',
        'relatedSortFields' => ['artist_name', 'label_name', 'genre_description', 'format_name'],
        'defaultRelatedSortDirection' => 'ASC',
    ];

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

    public function __construct(\PDO $db)
    {
        parent::__construct($db);
        $this->artistsHandler = new ArtistsHandler($db);
        $this->genresHandler = new GenresHandler($db);
        $this->labelsHandler = new LabelsHandler($db);
        $this->formatsHandler = new FormatsHandler($db);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function selectById(int $id)
    {
        if (!isset($id) || !TypeUtility::isInteger($id)) {
            $id = 0;
        }
        $query = 'SELECT ' . implode(self::$FIELDS['fields'], ',') . ' FROM album WHERE id = ' . $id;
        $result = $this->db->query($query);
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
     * @return array
     * @throws \Exception
     */
    public function select(GetParams $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::$FIELDS['sortFields'], self::$FIELDS['defaultSortField']);
        $sortDirection = $this->getSortDirectionFromParams($params, self::$FIELDS['defaultSortDirection']);
        $page = $params->page;
        $pageSize = $params->pageSize;

        $query = 'SELECT ' . implode(self::$FIELDS['fields'], ',') . ' FROM album';
        $query .= ' WHERE true';
        $query .= $this->getFilterClause($params);
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
     * @return array
     * @throws \Exception
     */
    public function getAlbumsSortedOnRelatedTable(GetParams $params)
    {
        $sortBy = $this->getSortByFromParams($params, self::$FIELDS['relatedSortFields'], 'id');
        $sortDirection = $this->getSortDirectionFromParams($params, self::$FIELDS['defaultRelatedSortDirection']);
        // sortBy is always formatted as table_field
        $relatedTable = explode('_', $sortBy)[0];
        $sortField = str_replace('_', '.', $sortBy);
        $selectFunc = function ($field) {
            return 'album.' . $field;
        };
        $selectFields = implode(array_map($selectFunc, self::$FIELDS['fields']), ',');
        $page = $params->page;
        $pageSize = $params->pageSize;

        $query = 'SELECT ' . $selectFields . ' FROM album';
        $query .= ' JOIN ' . $relatedTable . ' ON ' . $relatedTable . '.id = album.' . $relatedTable . '_id';
        $query .= ' WHERE true';
        $query .= $this->getFilterClause($params);
        $query .= ' ORDER BY ' . $sortField . ' ' . $sortDirection;
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
     * @return array | null
     * @throws \Exception
     */
    public function insert(array $albumData)
    {
        $this->validatePostData($albumData);
        $postData = $this->formatPostdataForInsert($albumData);
        $query = 'INSERT INTO album (' . $postData['keys'] . ')';
        $query .= ' VALUES (' . $postData['values'] . ')';
        $this->db->query($query);
        $id = $this->db->lastInsertId();
        return $this->selectById($id);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function update(int $id, array $albumData)
    {
        $this->validatePostData($albumData);
        $query = 'SELECT id FROM album WHERE id = ' . $id;
        if ($this->db->query($query)->rowCount() === 0) {
            return null;
        }
        $postData = $this->formatPostdataForUpdate($albumData);
        $query = 'UPDATE album SET ' . $postData . ' WHERE id = ' . $id;
        $this->db->query($query);
        return $this->selectById($id);
    }

    /**
     * Build part of WHERE clause depending on filter params.
     * @return string
     */
    private function getFilterClause(GetParams $params)
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
     * @return Album
     */
    private function createModelFromDatabaseData(array $albumData)
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
     * @throws \Exception
     */
    private function validatePostData(array $postData)
    {
        // general validation
        $this->validateMandatoryFields($postData, self::$FIELDS['mandatoryFields']);
        $this->validateKeys($postData, self::$FIELDS['fields']);

        // year should be 4 digits
        if (array_key_exists('year', $postData)) {
            $year = $postData['year'];
            if (!TypeUtility::isInteger($year) || (int)$year < 1900 || (int)$year > 4000) {
                throw new \Exception('Year should be a 4 digit number between 1900 and 4000.', 400);
            }
        }

        // check existence of artist, genre, label and format
        $artist = $this->artistsHandler->selectById($postData['artist_id'])['body'];
        $format = $this->formatsHandler->selectById($postData['format_id'])['body'];
        if (array_key_exists('label_id', $postData)) {
            $label = $this->labelsHandler->selectById($postData['label_id'])['body'];
        } else {
            $label = false;
        }
        if (array_key_exists('genre_id', $postData)) {
            $genre = $this->genresHandler->selectById($postData['genre_id'])['body'];
        } else {
            $genre = false;
        }
        foreach (['artist' => $artist, 'label' => $label, 'genre' => $genre, 'format' => $format] as $key => $entity) {
            if (!isset($entity)) {
                throw new \Exception(ucfirst($key) . ' with id ' . $postData[$key . '_id'] . ' cannot be found.', 400);
            }
        }
    }
}