<?php

namespace Templates;

use Models\Label;

class LabelTemplate
{
    /**
     * @var Label $label
     */
    protected $label;

    /**
     * LabelTemplate constructor.
     * @param Label $label
     */
    public function __construct($label)
    {
        $this->label = $label;
    }

    public function getArray()
    {
        return [
            'id' => $this->label->getId(),
            'name' => $this->label->getName(),
        ];
    }
}