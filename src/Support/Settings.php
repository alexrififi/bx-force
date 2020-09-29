<?php

namespace Medvinator\BxForce\Support;

use Bitrix\Main\Config\Configuration;

class Settings
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Settings constructor.
     *
     * @param string $module
     */
    public function __construct(string $module)
    {
        $this->data = Configuration::getInstance( $module );
    }

    /**
     * @param string $module
     * @return static
     */
    public static function make(string $module)
    {
        return new static( $module );
    }

    /**
     * Получить значение настройки
     *
     * @param string $path Путь до конкретной настройки. Доступна "dot" нотация
     * @return mixed
     */
    public function get(string $path)
    {
        return data_get( $this->data, $path );
    }
}
