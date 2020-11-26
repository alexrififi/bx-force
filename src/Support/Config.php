<?php

namespace Medvinator\BxForce\Support;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Configuration;
use Illuminate\Support\Str;

/**
 * @method static get(string $path)
 */
class Config
{
    protected $configurations;
    protected $module;

    /**
     * Handle dynamic static method calls
     *
     * @param string $method
     * @param array  $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        if ( $method === 'get' ) {
            return self::getInstance()->value( ...$parameters );
        }
        return self::getInstance()->$method( ...$parameters );
    }

    protected static function getInstance(): Config
    {
        static $instance = null;
        if ( $instance === null ) {
            $instance = new static();
        }
        return $instance;
    }

    public static function forModule(string $module): Config
    {
        $instance = new static;
        $instance->module = $module;
        return $instance;
    }

    /**
     * Получить значение настройки
     *
     * @param string $path Путь до конкретной настройки. Доступна "dot" нотация
     * @return array|mixed
     */
    public function value(string $path)
    {
        $module = $this->module ?? $this->resolveModule();

        if ( empty( $this->configurations[ $module ] ) ) {
            $this->configurations[ $module ] = Configuration::getInstance( $module );
        }

        return data_get( $this->configurations[ $module ], $path );
    }

    /**
     * @return string|null
     */
    protected function resolveModule(): ?string
    {
        $caller = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS )[2]['file'];

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
