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
            $album = [
                'id' => $this->album->getId(),
                'title' => $this->album->getTitle(),
                'year' => $this->album->getYear(),
                'date_added' => $this->album->getDateAddedString(),
                'image_thumb' => $this->album->getImageThumb(),
                'image_thumb_local' => $this->album->getImageThumbLocal(),
                'image' => $this->album->getImage(),
                'image_local' => $this->album->getImageLocal(),
                'image_fetch_timestamp' => $this->album->getImageFetchTimestampString(),
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