<?php

namespace Templates;

use Models\Format;

class FormatTemplate
{
    /**
     * @var Format $format
     */
    protected $format;

    /**
     * LabelTemplate constructor.
     * @param Format $format
     */
    public function __construct($format)
    {
        $this->format = $format;
    }

    public function getArray()
    {
        if (!isset($this->format)) {
            return null;
        }
        return [
            'id' => $this->format->getId(),
            'name' => $this->format->getName(),
            'description' => $this->format->getDescription(),
        ];
    }
}