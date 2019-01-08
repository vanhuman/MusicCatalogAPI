<?php

namespace Controllers;

use Psr\Container\ContainerInterface;

use Handlers\ArtistsHandler;
use Models\Artist;
use Templates\ArtistsTemplate;
use Templates\ArtistTemplate;

class ArtistsController extends Controller
{
    /**
     * ArtistsController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new ArtistsHandler($this->container->get('db'));
        $this->messageController = new MessageController();
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