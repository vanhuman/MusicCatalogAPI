<?php

namespace Templates;

use Models\Genre;

class GenreTemplate implements TemplateInterface
{
    /**
     * @var
     */
    protected $genre;

    /**
     * GenreTemplate constructor.
     * @param Genre $genre
     */
    public function __construct($genre)
    {
        $this->genre = $genre;
    }

    public function getArray()
    {
        return [
            'id' => $this->genre->getId(),
            'description' => $this->genre->getDescription(),
            'notes' => $this->genre->getNotes(),
        ];
    }
}