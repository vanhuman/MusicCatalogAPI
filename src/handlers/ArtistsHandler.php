<?php

namespace Handlers;

use Models\Artist;

class ArtistsHandler extends Database
{
    const FIELDS = ['id', 'name'];

    /**
     * @param int $artistId
     * @throws \Exception
     * @return Artist $artist
     */
    public function getArtist($artistId)
    {
        $query = 'SELECT ' . implode(self::FIELDS, ',') . ' FROM artist WHERE id = ' . $artistId;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        $artistData = $result->fetch();
        return new Artist($artistData);
    }
}