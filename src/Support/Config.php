<?php

namespace Medvinator\BxForce\Support;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Configuration;
use Illuminate\Support\Str;

class Config
{
    protected static $modules = [];

    /**
     * Получить значение настройки
     *
     * @param string $path Путь до конкретной настройки. Доступна "dot" нотация
     * @return array|mixed
     */
    public static function get(string $path)
    {
        $module = self::resolveModule();

        if ( empty( self::$modules[ $module ] ) ) {
            self::$modules[ $module ] = Configuration::getInstance( $module );
        }

        return data_get( self::$modules[ $module ], $path );
    }

    /**
     * @return string|null
     */
    protected static function resolveModule(): ?string
    {
        $caller = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS )[1]['file'];

        if ( Str::contains( $caller, 'local/components' ) ) {
            $slice = Str::after( $caller, Application::getDocumentRoot() . '/local/components/' );
            $parts = explode( '/', $slice );
            return $parts[0] . '.' . Str::before( $parts[1], '.' );
        }

        if ( Str::contains( $caller, 'local/modules' ) ) {
            $slice = Str::after( $caller, Application::getDocumentRoot() . '/local/modules/' );
            $parts = explode( '/', $slice );
            return $parts[0];
        }

        return null;
    }
}
