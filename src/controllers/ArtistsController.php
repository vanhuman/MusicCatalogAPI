<?php

namespace Controllers;

use Psr\Container\ContainerInterface;

use Handlers\ArtistsHandler;
use Models\Artist;
use Templates\ArtistsTemplate;
use Templates\ArtistTemplate;

class ArtistsController extends RestController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->handler = new ArtistsHandler($this->container->get('db'));
    }

    /**
     * @param Artist | Artist[] $artists
     * @return ArtistsTemplate | ArtistTemplate
     */
    protected function newTemplate($artists)
    {
        if (is_array($artists)) {
            return new ArtistsTemplate($artists);
        } else {
            return new ArtistTemplate($artists);
        }
    }
}
