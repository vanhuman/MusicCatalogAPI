<?php

namespace Templates;

use Models\Label;

class LabelTemplate implements TemplateInterface
{
    /**
     * @var Label $label
     */
    protected $label;

    public function __construct(Label $label = null)
    {
        $this->label = $label;
    }

    /**
     * @return array|null
     */
    public function getArray(bool $includeWrapper = true)
    {
        if (isset($this->label)) {
            $label = [
                'id' => $this->label->getId(),
                'name' => $this->label->getName(),
            ];
        } else {
            $label = null;
        }
        if ($includeWrapper) {
            $label = [
                'label' => $label
            ];
        }
        return $label;
    }
}