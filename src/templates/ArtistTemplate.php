<?php

namespace Templates;

use Models\Artist;

class ArtistTemplate implements TemplateInterface
{
    /**
     * @var Artist $artist
     */
    protected $artist;

    /**
     * ArtistTemplate constructor.
     * @param Artist $artist
     */
    public function __construct($artist)
    {
        $this->artist = $artist;
    }

    public function getArray()
    {
        if (!isset($this->artist)) {
            return null;
        }
        return [
            'id' => $this->artist->getId(),
            'name' => $this->artist->getName(),
        ];
    }
}