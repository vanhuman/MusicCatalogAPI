<?php

namespace Handlers;

use Models\Album;
use Models\Artist;
use Models\Format;
use Models\Label;
use Models\Genre;

class AlbumsHandler extends Database
{
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
        return $this->createAlbumFromApiData($albumData);
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
            $newAlbum = $this->createAlbumFromApiData($albumData);
            $albums[] = $newAlbum;
        }
        return isset($albums) ? $albums : [];
    }

    private function createAlbumFromApiData($albumData)
    {
        $newAlbum = new Album([
            'id' => $albumData['album_id'],
            'title' => $albumData['album_title'],
            'year' => $albumData['album_year'],
        ]);
        $newAlbum->setArtist(new Artist([
            'name' => $albumData['artist_name'],
        ]));
        $newAlbum->setGenre(new Genre([
            'description' => $albumData['genre_description'],
        ]));
        $newAlbum->setLabel(new Label([
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
     * @return string
     */
    private function getSelectFields()
    {
        $selectFields = [
            'album' => ['id', 'title', 'year'],
            'artist' => ['name'],
            'label' => ['name'],
            'genre' => ['description'],
            'format' => ['name', 'description']
        ];
        foreach ($selectFields as $table => $fields) {
            foreach ($fields as $field) {
                $selectFieldsArray[] = $table . '.' . $field . ' as ' . $table . '_' . $field;
            }
        }
        return implode($selectFieldsArray, ',');
    }
}