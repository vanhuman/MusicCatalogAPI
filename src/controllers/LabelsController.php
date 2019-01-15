<?php

namespace Controllers;

use Psr\Container\ContainerInterface;

use Handlers\LabelsHandler;
use Models\Label;
use Templates\LabelsTemplate;
use Templates\LabelTemplate;

class LabelsController extends BaseController
{
    /**
     * LabelsController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new LabelsHandler($this->container->get('db'));
        $this->messageController = new MessageController();
    }

    /**
     * @param Label | Label[] $labels
     * @return LabelsTemplate | LabelTemplate
     */
    protected function newTemplate($labels)
    {
        if (is_array($labels)) {
            return new LabelsTemplate($labels);
        } else {
            return new LabelTemplate($labels);
        }
    }
}