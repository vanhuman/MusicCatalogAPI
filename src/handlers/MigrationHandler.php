<?php


namespace Handlers;


class MigrationHandler extends Database
{
    /**
     * Migration to move label names from the album table into its own table
     * @return int | \Exception
     */
    public function migrateLabels()
    {
        $queryAllAlbums = 'SELECT album.id, album.label FROM album';
        try {
            $albums = $this->db->query($queryAllAlbums)->fetchAll();
        } catch (\Exception $e) {
            return $e;
        }
        foreach ($albums as $album) {
            $labelName = $album['label'];
            $queryGetLabel = 'SELECT * FROM label WHERE label.name = "' . $labelName. '"';
            $label = $this->db->query($queryGetLabel)->fetch();
            if (!$label) {
                // create label record
                $queryInsertLabel = 'INSERT' . ' INTO label (name)';
                $queryInsertLabel .= ' VALUES ("' . $labelName . '")';
                std()->show($queryInsertLabel);
                $this->db->query($queryInsertLabel);
                $queryGetCreatedLabel = 'SELECT * FROM label WHERE label.name = "' . $labelName . '"';
                $label = $this->db->query($queryGetCreatedLabel)->fetch();
                $labelId = $label['id'];
            } else {
                $labelId = $label['id'];
            }
            // update album with label id
            $queryUpdateAlbum = 'UPDATE album';
            $queryUpdateAlbum .= ' SET album.label_id = ' . $labelId;
            $queryUpdateAlbum .= ' WHERE album.id = ' . $album['id'];
            std()->show($queryUpdateAlbum);
            $this->db->query($queryUpdateAlbum);
        }
        return sizeof($albums);
    }

    /**
     * Migration to move artist names from the album table into its own table
     * @return int | \Exception
     */
    public function migrateArtists()
    {
        $queryAllAlbums = 'SELECT album.id, album.artist FROM album';
        try {
            $albums = $this->db->query($queryAllAlbums)->fetchAll();
        } catch (\Exception $e) {
            return $e;
        }
        foreach ($albums as $album) {
            $artistName = $album['artist'];
            $queryGetArtist = 'SELECT * FROM artist WHERE artist.name = "' . $artistName. '"';
            $artist = $this->db->query($queryGetArtist)->fetch();
            if (!$artist) {
                // create artist record
                $queryInsertArtist = 'INSERT' . ' INTO artist (name)';
                $queryInsertArtist .= ' VALUES ("' . $artistName . '")';
                std()->show($queryInsertArtist);
                $this->db->query($queryInsertArtist);
                $queryGetCreatedArtist = 'SELECT * FROM artist WHERE artist.name = "' . $artistName . '"';
                $artist = $this->db->query($queryGetCreatedArtist)->fetch();
                $artistId = $artist['id'];
            } else {
                $artistId = $artist['id'];
            }
            // update album with artist id
            $queryUpdateAlbum = 'UPDATE album';
            $queryUpdateAlbum .= ' SET album.artist_id = ' . $artistId;
            $queryUpdateAlbum .= ' WHERE album.id = ' . $album['id'];
            std()->show($queryUpdateAlbum);
            $this->db->query($queryUpdateAlbum);
        }
        return sizeof($albums);
    }
}