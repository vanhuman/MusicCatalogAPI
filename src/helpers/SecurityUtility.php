<?php

namespace Helpers;

class SecurityUtility
{
    /**
     * @param int $length
     * @return bool|string
     * @throws \Exception
     */
    public static function generateToken($length = 40)
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    public static function generateTimeOut()
    {
        return time() + 14400;
    }
}