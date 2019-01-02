<?php

namespace Handlers;

use Helpers\DatabaseConnection;

class MigrationHandler extends DatabaseConnection
{
    /**
     * @throws \Exception
     */
    public function migrationPre()
    {
        try {
            // rename tables: cds into album, cdsgenre into genre
            $query = 'ALTER TABLE cds RENAME album';
            $this->db->query($query);
            $query = 'ALTER TABLE cdsgenre RENAME genre';
            $this->db->query($query);

            // add tables: artist, format, label
            $query = 'CREATE TABLE artist (id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, name varchar(255) NOT NULL)';
            $this->db->query($query);
            $query = 'CREATE TABLE label (id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, name varchar(255) NOT NULL)';
            $this->db->query($query);
            $query = 'CREATE TABLE format (id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, name varchar(255) NOT NULL, description varchar(255) NOT NULL)';
            $this->db->query($query);
            $query = 'INSERT INTO format (id, name, description) VALUES';
            $query .= ' (1, "CD", ""), (2, "LP", ""), (3, "EP", ""), (4, "DVD", ""), (5, "MP3", "Any digital format"), (6, "CAS", "Cassette")';
            $this->db->query($query);
            $query = 'ALTER TABLE genre CHANGE descr description VARCHAR(255)';
            $this->db->query($query);
            $query = 'ALTER TABLE genre CHANGE descr_ext notes VARCHAR(255)';
            $this->db->query($query);

            // change album field names: genre into genre_id (also change format), media into format_id, more into notes
            $query = 'UPDATE album SET album.genre = 0 WHERE album.genre = ""';
            $this->db->query($query);
            $query = 'ALTER TABLE album CHANGE genre genre_id INT(11)';
            $this->db->query($query);
            $query = 'ALTER TABLE album CHANGE media format_id INT(11)';
            $this->db->query($query);
            $query = 'ALTER TABLE album CHANGE more notes VARCHAR(2048)';
            $this->db->query($query);

            // remove album field frontpage
            $query = 'ALTER TABLE album DROP frontpage';
            $this->db->query($query);

            // add album fields: artist_id and label_id
            $query = 'ALTER TABLE album ADD artist_id INT(11) NOT NULL';
            $this->db->query($query);
            $query = 'ALTER TABLE album ADD label_id INT(11) NOT NULL';
            $this->db->query($query);

            // change album date field: format, name and default
            $query = 'ALTER TABLE album CHANGE date date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP';
            $this->db->query($query);

            // add indexes
            $query = 'ALTER TABLE genre ADD INDEX(description)';
            $this->db->query($query);
            $query = 'ALTER TABLE label ADD INDEX(name)';
            $this->db->query($query);
            $query = 'ALTER TABLE format ADD INDEX(name)';
            $this->db->query($query);
            $query = 'ALTER TABLE artist ADD INDEX(name)';
            $this->db->query($query);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }

    /**
     * @throws \Exception
     */
    public function migrationPost()
    {
        try {
            // rename album fields
            $query = 'ALTER TABLE album CHANGE artist artist_deprecated VARCHAR(255)';
            $this->db->query($query);
            $query = 'ALTER TABLE album CHANGE label label_deprecated VARCHAR(255)';
            $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }

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
            $queryGetLabel = 'SELECT * FROM label WHERE label.name = "' . $labelName . '"';
            $label = $this->db->query($queryGetLabel)->fetch();
            if (!$label) {
                // create label record
                $queryInsertLabel = 'INSERT' . ' INTO label (name)';
                $queryInsertLabel .= ' VALUES ("' . $labelName . '")';
                std()->show($queryInsertLabel);
                $this->db->query($queryInsertLabel);
                $label = $this->db->query($queryGetLabel)->fetch();
            }
            $labelId = $label['id'];
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
            $queryGetArtist = 'SELECT * FROM artist WHERE artist.name = "' . $artistName . '"';
            $artist = $this->db->query($queryGetArtist)->fetch();
            if (!$artist) {
                // create artist record
                $queryInsertArtist = 'INSERT' . ' INTO artist (name)';
                $queryInsertArtist .= ' VALUES ("' . $artistName . '")';
                std()->show($queryInsertArtist);
                $this->db->query($queryInsertArtist);
                $artist = $this->db->query($queryGetArtist)->fetch();
            }
            $artistId = $artist['id'];
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