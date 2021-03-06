<?php

namespace Controllers;

use Helpers\ContainerHelper;
use Psr\Container\ContainerInterface;

use Models\Format;
use Templates\FormatsTemplate;
use Templates\FormatTemplate;

class FormatsController extends RestController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->handler = ContainerHelper::get($container, 'formatsHandler');
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
