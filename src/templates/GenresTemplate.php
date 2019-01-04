<?php

namespace Templates;

use Models\Genre;

class GenresTemplate implements TemplateInterface
{
    /**
     * @var $genre Genre[]
     */
    protected $genres;

    /**
     * AlbumsTemplate constructor.
     * @param $genres Genre[]
     */
    public function __construct($genres)
    {
        $this->genres = $genres;
    }

    /**
     * @return array
     */
    public function getArray() {
        foreach ($this->genres as $genre) {
            $genreTemplate = new GenreTemplate($genre);
            $genresArray[] = $genreTemplate->getArray(false);
        }
        if (!isset($genresArray)) {
            $genresArray = [];
        }
        $genres = [
            'genres' => $genresArray
        ];
        return $genres;
    }

}