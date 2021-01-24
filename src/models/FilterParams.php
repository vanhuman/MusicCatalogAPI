<?php

namespace Models;

class FilterParams
{
    /**
     * @var int $artist_id
     */
    public $artist_id;

    /**
     * @var int $genre_id
     */
    public $genre_id;

    /**
     * @var int $label_id
     */
    public $label_id;

    /**
     * @var int $format_id
     */
    public $format_id;

    /**
     * FilterInterface constructor.
     * @param array $args
     */
    public function __construct(array $args)
    {
        foreach ($args as $property => $value) {
            $this->{$property} = $value;
        }
    }
}