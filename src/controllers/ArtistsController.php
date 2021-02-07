<?php

namespace Controllers;

use Helpers\ContainerHelper;
use Psr\Container\ContainerInterface;

use Models\Artist;
use Templates\ArtistsTemplate;
use Templates\ArtistTemplate;

class ArtistsController extends RestController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->handler = ContainerHelper::get($container, 'artistsHandler');
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
