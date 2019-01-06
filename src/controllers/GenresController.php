<?php

namespace Controllers;

use Psr\Container\ContainerInterface;

use Handlers\GenresHandler;
use Models\Genre;
use Templates\GenresTemplate;
use Templates\GenreTemplate;

class GenresController extends Controller
{
    /**
     * GenresController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new GenresHandler($this->container->get('db'));
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