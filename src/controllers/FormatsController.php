<?php

namespace Controllers;

use Psr\Container\ContainerInterface;

use Handlers\FormatsHandler;
use Models\Format;
use Templates\FormatsTemplate;
use Templates\FormatTemplate;

class FormatsController extends BaseController
{
    /**
     * FormatsController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new FormatsHandler($this->container->get('db'));
        $this->messageController = new MessageController();
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