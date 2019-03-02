<?php

namespace Templates;

use Models\Album;

class AlbumTemplate implements TemplateInterface
{
    /**
     * @var $album Album
     */
    protected $album;

    public function __construct(Album $album = null)
    {
        $this->album = $album;
    }

    /**
     * @return array
     */
    public function getArray(bool $includeWrapper = true)
    {
        if (isset($this->album)) {
            std()->show($this->album);
            $album = [
                'id' => $this->album->getId(),
                'title' => $this->album->getTitle(),
                'year' => $this->album->getYear(),
                'date_added' => $this->album->getDateAddedString(),
                'image_thumb' => $this->album->getImageThumb(),
                'image' => $this->album->getImage(),
                'artist' => (new ArtistTemplate($this->album->getArtist()))->getArray(false),
                'genre' => (new GenreTemplate($this->album->getGenre()))->getArray(false),
                'label' => (new LabelTemplate($this->album->getLabel()))->getArray(false),
                'format' => (new FormatTemplate($this->album->getFormat()))->getArray(false),
                'notes' => $this->album->getNotes(),
            ];
        } else {
            $album = null;
        }
        if ($includeWrapper) {
            $album = [
                'album' => $album
            ];
        }
        return $album;
    }
}