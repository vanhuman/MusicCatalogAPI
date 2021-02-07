<?php

namespace Controllers;

use Helpers\ContainerHelper;
use Psr\Container\ContainerInterface;

use Models\Genre;
use Templates\GenresTemplate;
use Templates\GenreTemplate;

class GenresController extends RestController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->handler = ContainerHelper::get($container, 'genresHandler');
    }

    /**
     * @param Genre | Genre[] $genres
     * @return GenresTemplate | GenreTemplate
     */
    protected function newTemplate($genres)
    {
        if (is_array($genres)) {
            return new GenresTemplate($genres);
        } else {
            return new GenreTemplate($genres);
        }
    }
}
