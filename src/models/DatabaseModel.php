<?php

namespace Models;

class DatabaseModel
{
    /**
     * @var int $id
     */
    protected $id;

    /**
     * DatabaseModel constructor.
     * @param $array
     */
    public function __construct($array)
    {
        $this->setFromArray($array);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = (int)$id;
    }

    /**
     * Fill model with array from the database
     * @param array $array
     */
    public function setFromArray($array) {
        foreach ($array as $property => $value) {
            $function = 'set' . ucfirst($property);
            if (is_callable([$this, $function])) {
                $this->$function($value);
            }
        }
    }

    /**
     * @return array
     */
    public function toArray() {
        foreach (get_object_vars($this) as $property => $value) {
            $array[$property] = $value;
        }
        return isset($array) ? $array : [];
    }
}