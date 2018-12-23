<?php

namespace Templates;

use Models\Album;

class AlbumsTemplate
{
    /**
     * @var $albums Album[]
     */
    protected $albums;

    /**
     * AlbumsTemplate constructor.
     * @param $albums Album[]
     */
    public function __construct($albums)
    {
        $this->albums = $albums;
    }

    /**
     * @return array
     */
    public function getObject() {
        foreach ($this->albums as $album) {
            $albumTemplate = new AlbumTemplate($album);
            $albumsObject[] = $albumTemplate->getObject();
        }
        return isset($albumsObject) ? $albumsObject : [];
    }

}