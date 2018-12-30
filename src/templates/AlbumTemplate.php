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
        if (!isset($this->album)) {
            return null;
        }
        return [
            'album' => [
                'id' => $this->album->getId(),
                'title' => $this->album->getTitle(),
                'year' => $this->album->getYear(),
                'dateAdded' => $this->album->getDateAddedString(),
                'notes' => $this->album->getNotes(),
                'artist' => (new ArtistTemplate($this->album->getArtist()))->getArray(false),
                'genre' => (new GenreTemplate($this->album->getGenre()))->getArray(false),
                'label' => (new LabelTemplate($this->album->getLabel()))->getArray(false),
                'format' => (new FormatTemplate($this->album->getFormat()))->getArray(false),
            ]
        ];
    }
}