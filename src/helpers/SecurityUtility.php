<?php

namespace Helpers;

class SecurityUtility
{
    /**
     * @return bool|string
     * @throws \Exception
     */
    public static function generateToken(int $length = 40)
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    /**
     * @return int
     */
    public static function generateTimeOut()
    {
        return time() + 14400;
    }
}