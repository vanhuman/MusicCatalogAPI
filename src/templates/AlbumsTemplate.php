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
    public function getArray() {
        foreach ($this->albums as $album) {
            $albumTemplate = new AlbumTemplate($album);
            $albumsObject[] = $albumTemplate->getArray();
        }
        return isset($albumsObject) ? $albumsObject : [];
    }

}