<?php

namespace Templates;

use Models\Album;

class AlbumTemplate
{
    /**
     * @var $album Album
     */
    protected $album;

    /**
     * AlbumTemplate constructor.
     * @param $album Album
     */
    public function __construct($album)
    {
        $this->album = $album;
    }

    /**
     * @return array
     */
    public function getObject() {
        return [
            'id' => $this->album->getId(),
            'artist' => $this->album->getArtist()->getName(),
            'title' => $this->album->getTitle(),
            'year' => $this->album->getYear(),
            'mood' => $this->album->getGenre()->getDescription(),
            'label' => $this->album->getLabel()->getName(),
            'format' => $this->album->getFormat()->getName(),
        ];
    }
}