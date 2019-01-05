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

    /**
     * @param bool $includeWrapper
     * @return array|null
     */
    public function getArray($includeWrapper = true)
    {
        if (isset($this->genre)) {
            $genre = [
                'id' => $this->genre->getId(),
                'description' => $this->genre->getDescription(),
                'notes' => $this->genre->getNotes(),
            ];
        }
        if ($includeWrapper) {
            $genre = [
                'genre' => $genre
            ];
        }
        return $genre;
    }
}