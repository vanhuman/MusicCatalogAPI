<?php

namespace Templates;

use Models\Format;

class FormatTemplate implements TemplateInterface
{
    /**
     * @var Format $format
     */
    protected $format;

    public function __construct(Format $format = null)
    {
        $this->format = $format;
    }

    /**
     * @return array|null
     */
    public function getArray(bool $includeWrapper = true)
    {
        if (isset($this->format)) {
            $format = [
                'id' => $this->format->getId(),
                'name' => $this->format->getName(),
                'description' => $this->format->getDescription(),
            ];
        } else {
            $format = null;
        }
        if ($includeWrapper) {
            $format = [
                'format' => $format
            ];
        }
        return $format;
    }
}