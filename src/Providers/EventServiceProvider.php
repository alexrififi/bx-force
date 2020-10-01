<?php

namespace Medvinator\BxForce\Providers;

use Bitrix\Main\EventManager;

class EventServiceProvider
{
    protected const BX_MODULES = [
        'iblock',
        'main',
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

    public static function make(): void
    {
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
