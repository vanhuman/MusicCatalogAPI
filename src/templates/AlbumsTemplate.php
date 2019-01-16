<?php

namespace Templates;

use Models\Album;

class AlbumsTemplate implements TemplateInterface
{
    /**
     * @var $albums Album[]
     */
    protected $albums;

    /**
     * @param $albums Album[]
     */
    public function __construct($albums)
    {
        $this->albums = $albums;
    }

    /**
     * @return array
     */
    public function getArray() {
        foreach ($this->albums as $album) {
            $albumTemplate = new AlbumTemplate($album);
            $albumsArray[] = $albumTemplate->getArray(false);
        }
        if (!isset($albumsArray)) {
            $albumsArray = [];
        }
        $albums = [
            'albums' => $albumsArray
        ];
        return $albums;
    }

}