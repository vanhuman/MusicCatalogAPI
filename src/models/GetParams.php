<?php

namespace Models;

class GetParams
{
    /**
     * @var int $page
     */
    public $page = 1;

    /**
     * @var int $pageSize
     */
    public $pageSize = 50;

    /**
     * @var string $sortBy
     */
    public $sortBy;

    /**
     * @var string $sortDirection
     */
    public $sortDirection;

    /**
     * @var FilterParams $filter
     */
    public $filter;

    /**
     * @var string $keywords
     */
    public $keywords;

    /**
     * ParamsInterface constructor.
     * @param array $args
     */
    public function __construct(array $args)
    {
        foreach ($args as $property => $value) {
            if ($property !== 'filter') {
                $this->{$property} = $value;
            } else {
                $this->{$property} = new FilterParams($value);
            }
        }
        if (!isset($this->filter)) {
            $this->filter = new FilterParams([]);
        }
    }
}