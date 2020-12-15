<?php

namespace Medvinator\BxForce\Providers;

use Bitrix\Main\EventManager;
use function defined;

class EventServiceProvider
{
    protected const BX_MODULES = [
        'iblock',
        'main',
        'pull',
        'sale',
        'search',
    ];

    /**
     * @var EventManager
     */
    protected $eventManager;

    public function __construct()
    {
        $this->eventManager = EventManager::getInstance();
    }

    /**
     * @param string|null $module
     */
    public static function make($module = null): void
    {
        if ( defined( 'DisableEventsCheck' ) && DisableEventsCheck ) {
            return;
        }

        if ( $module ) {
            $moduleServiceProviderClass = ucwords( str_replace( '.', ' ', $module ) );
            $moduleServiceProviderClass = str_replace( ' ', '\\', $moduleServiceProviderClass ) . '\\Providers\\EventServiceProvider';
            if ( class_exists( $moduleServiceProviderClass ) ) {
                (new $moduleServiceProviderClass)->boot();
                return;
            }
        }

        (new static())->boot();
    }

    /**
     * Регистрация обработчиков событий
     */
    protected function boot(): void
    {
        foreach (self::BX_MODULES as $module) {
            if ( method_exists( $this, $module ) ) {
                $this->$module();
            }
        }
    }

    /**
     * @param string         $module
     * @param string         $event
     * @param array|callable $callback
     */
    protected function register(string $module, string $event, $callback): void
    {
        $this->eventManager->addEventHandler( $module, $event, $callback );
    }
}
