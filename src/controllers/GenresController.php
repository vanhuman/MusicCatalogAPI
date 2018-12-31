<?php

namespace Controllers;

use Handlers\GenresHandler;
use Psr\Container\ContainerInterface;
use Templates\GenresTemplate;
use Templates\GenreTemplate;

class GenresController extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new GenresHandler($this->container->get('db'));
    }

    protected function newTemplate($genres)
    {
        if (is_array($genres)) {
            return new GenresTemplate($genres);
        } else {
            return new GenreTemplate($genres);
        }
    }
}