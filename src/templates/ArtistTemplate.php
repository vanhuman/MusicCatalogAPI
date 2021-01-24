<?php

namespace Templates;

use Models\Artist;

class ArtistTemplate implements TemplateInterface
{
    /**
     * @var Artist $artist
     */
    protected $artist;

    public function __construct(Artist $artist = null)
    {
        $this->artist = $artist;
    }

    /**
     * @return array|null
     */
    public function getArray(bool $includeWrapper = true)
    {
        if (isset($this->artist)) {
            $artist = [
                'id' => $this->artist->getId(),
                'name' => $this->artist->getName(),
            ];
        } else {
            $artist = null;
        }
        if ($includeWrapper) {
            $artist = [
                'artist' => $artist
            ];
        }
        return $artist;
    }
}