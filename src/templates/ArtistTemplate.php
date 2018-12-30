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

    /**
     * @param bool $includeWrapper
     * @return array|null
     */
    public function getArray($includeWrapper = true)
    {
        if (!isset($this->artist)) {
            return null;
        }
        $artist = [
            'id' => $this->artist->getId(),
            'name' => $this->artist->getName(),
        ];
        if ($includeWrapper) {
            $artist = [
                'artist' => $artist
            ];
        }
        return $artist;
    }
}