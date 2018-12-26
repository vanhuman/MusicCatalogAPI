<?php

namespace Templates;

use Models\Album;

class AlbumTemplate implements TemplateInterface
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
    public function getArray()
    {
        return [
            'album' => [
                'id' => $this->album->getId(),
                'title' => $this->album->getTitle(),
                'year' => $this->album->getYear(),
                'date' => $this->album->getDateString(),
                'notes' => $this->album->getNotes(),
                'artist' => (new ArtistTemplate($this->album->getArtist()))->getArray(),
                'genre' => (new GenreTemplate($this->album->getGenre()))->getArray(),
                'label' => (new LabelTemplate($this->album->getLabel()))->getArray(),
                'format' => $this->album->getFormat()->getName(),
            ]
        ];
    }
}