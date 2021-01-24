<?php

namespace Templates;

use Models\Genre;

class GenreTemplate implements TemplateInterface
{
    /**
     * @var
     */
    protected $genre;

    public function __construct(Genre $genre = null)
    {
        $this->genre = $genre;
    }

    /**
     * @return array|null
     */
    public function getArray(bool $includeWrapper = true)
    {
        if (isset($this->genre)) {
            $genre = [
                'id' => $this->genre->getId(),
                'description' => $this->genre->getDescription(),
                'notes' => $this->genre->getNotes(),
            ];
        } else {
            $genre = null;
        }
        if ($includeWrapper) {
            $genre = [
                'genre' => $genre
            ];
        }
        return $genre;
    }
}