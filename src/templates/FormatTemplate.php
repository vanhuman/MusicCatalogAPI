<?php

namespace Templates;

use Models\Format;

class FormatTemplate implements TemplateInterface
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

    /**
     * @param bool $includeWrapper
     * @return array|null
     */
    public function getArray($includeWrapper = true)
    {
        if (isset($this->format)) {
            $format = [
                'id' => $this->format->getId(),
                'name' => $this->format->getName(),
                'description' => $this->format->getDescription(),
            ];
        }
        if ($includeWrapper) {
            $format = [
                'format' => $format
            ];
        }
        return $format;
    }
}