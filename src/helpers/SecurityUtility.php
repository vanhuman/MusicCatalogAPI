<?php

namespace Helpers;

class SecurityUtility
{
    private static $secureKey = "MIICXAIBAAKBgGUOxAsjCpBnXVhulL16lWa9m5TPEcQsa6bujv/MHmbS7xCjX7rC
tC9NqoRirhWwUt0kkREryyXut7CSMNVfAY6VmE3pSQhaxqatsBWh2y3Pw+jFskSx
v5Jy/OraNw1PEGSyXrf3R+w60lYGmULWFQRgGFkR/exAR8TFLQYHAbSxAgMBAAEC
gYBBcgCP3c+XmPkgrexxkRN0B9Lb2gX9b28AGnpNOhluKahctBDo1/Lst3V4apem
wBvJz0aLQjs7g48+ME6jv/U3WpW9Ig6BVHXXHBLC9D2ie+l15oZTDNJp4UW9qzx6
28TkXO647CLaOM0zr/SVYB7FthImbQp3nYJ6zXA5hVO1iQJBAK+IaDvmplUVjJUa
MQ62/0ED2qmHetArYKVL4/gTFV2UZtsZ9c9dP/RIIjYSfFjIvgCZDGmfrIqOG3d3
Fa1NB1sCQQCTYlphiwppPrfdASrmKLByw1+r7UFQr+mLfTuFzi8rKVsLcub7DcaQ
ZW4Jxz8xW3CUBc4B9WOGz0Pl6PMgsr3jAkEAovHIeNqqy07+uqaepZ9AonTWdgs6
+aVayPcC/5WPftg1Bu05Xy6cIMMZZwKQBccLgfiRKje8br5uRt7383EPAwJANmVK
bk3e3dEkExFnP+MsnG63VMEQJjOGwtDJsyzSA+ATljomPeprqseEwV8kV5ckCb3K
trZWsV619JjxgtP1eQJBAI9Td/bO0XNF4NalQJp9pEP/qHEuxsW1NgFMwCHEpZsZ
iIZuZtKgrtzAczziLYtvtCTrypxfGSwami7zZ2EMmhs=";

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
        return time() + 600;
    }

    public static function hash($password, $salt): string
    {
        return hash_hmac('sha256', $password . $salt, self::$secureKey);
    }
}
