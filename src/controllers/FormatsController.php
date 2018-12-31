<?php

namespace Controllers;

use Handlers\FormatsHandler;
use Psr\Container\ContainerInterface;
use Templates\FormatsTemplate;
use Templates\FormatTemplate;

class FormatsController extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new FormatsHandler($this->container->get('db'));
    }

    protected function newTemplate($formats)
    {
        if (is_array($formats)) {
            return new FormatsTemplate($formats);
        } else {
            return new FormatTemplate($formats);
        }
    }
}