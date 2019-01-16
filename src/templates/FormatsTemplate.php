<?php

namespace Templates;

use Models\Format;

class FormatsTemplate implements TemplateInterface
{
    /**
     * @var $format Format[]
     */
    protected $formats;

    /**
     * @param $formats Format[]
     */
    public function __construct($formats)
    {
        $this->formats = $formats;
    }

    /**
     * @return array
     */
    public function getArray() {
        foreach ($this->formats as $format) {
            $formatTemplate = new FormatTemplate($format);
            $formatsArray[] = $formatTemplate->getArray(false);
        }
        if (!isset($formatsArray)) {
            $formatsArray = [];
        }
        $formats = [
            'formats' => $formatsArray
        ];
        return $formats;
    }

}