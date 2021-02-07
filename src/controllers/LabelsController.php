<?php

namespace Controllers;

use Helpers\ContainerHelper;
use Psr\Container\ContainerInterface;

use Models\Label;
use Templates\LabelsTemplate;
use Templates\LabelTemplate;

class LabelsController extends RestController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->handler = ContainerHelper::get($container, 'labelsHandler');
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
