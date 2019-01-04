<?php

namespace Templates;

use Models\Label;

class LabelsTemplate implements TemplateInterface
{
    /**
     * @var $label Label[]
     */
    protected $labels;

    /**
     * AlbumsTemplate constructor.
     * @param $labels Label[]
     */
    public function __construct($labels)
    {
        $this->labels = $labels;
    }

    /**
     * @return array
     */
    public function getArray() {
        foreach ($this->labels as $label) {
            $labelTemplate = new LabelTemplate($label);
            $labelsArray[] = $labelTemplate->getArray(false);
        }
        if (!isset($labelsArray)) {
            $labelsArray = [];
        }
        $labels = [
            'labels' => $labelsArray
        ];
        return $labels;
    }

}