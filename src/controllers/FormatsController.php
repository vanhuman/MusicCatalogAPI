<?php

namespace Controllers;

use Psr\Container\ContainerInterface;

use Handlers\FormatsHandler;
use Models\Format;
use Templates\FormatsTemplate;
use Templates\FormatTemplate;

class FormatsController extends RestController
{
    public function __construct(ContainerInterface $container)
    {
        $this->initController($container);
        $this->handler = new FormatsHandler($this->container->get('db'));
    }

    /**
     * @param Format | Format[] $formats
     * @return FormatsTemplate | FormatTemplate
     */
    protected function newTemplate($formats)
    {
        if (is_array($formats)) {
            return new FormatsTemplate($formats);
        } else {
            return new FormatTemplate($formats);
        }
    }
}