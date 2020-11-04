<?php

namespace Medvinator\BxForce\Support;

use Bitrix\Main\Config\Configuration;

class Environment
{
    /**
     * @return bool
     */
    public static function isDebug(): bool
    {
        return Configuration::getValue( 'exception_handling' )['debug'];
    }

    /**
     * @return bool
     */
    public static function isProduction(): bool
    {
        return !self::isDebug();
    }
}
