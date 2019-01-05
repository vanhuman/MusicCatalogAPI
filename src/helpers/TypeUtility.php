<?php

namespace Helpers;

class TypeUtility
{
    /**
     * Determines if the input is an integer or can be converted to an integer.
     * Null and '' are not integers, '23' and 23 are integers.
     * @param mixed $input
     * @return bool
     */
    static public function isInteger($input)
    {
        return ctype_digit(strval($input));
    }
}