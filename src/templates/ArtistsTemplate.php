<?php

namespace Templates;

use Models\Artist;

class ArtistsTemplate implements TemplateInterface
{
    /**
     * @var $artist Artist[]
     */
    protected $artists;

    /**
     * AlbumsTemplate constructor.
     * @param $artists Artist[]
     */
    public function __construct($artists)
    {
        $this->artists = $artists;
    }

    /**
     * @return array
     */
    public function getArray() {
        foreach ($this->artists as $artist) {
            $artistTemplate = new ArtistTemplate($artist);
            $artistsArray[] = $artistTemplate->getArray(false);
        }
        if (!isset($artistsArray)) {
            $artistsArray = [];
        }
        $artists = [
            'artists' => $artistsArray
        ];
        return $artists;
    }

}