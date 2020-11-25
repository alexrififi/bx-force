<?php

namespace Medvinator\BxForce\Support;

use Bitrix\Main\Application;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Log
 *
 * @mixin Logger
 * @link https://seldaek.github.io/monolog/
 */
class Log
{
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        string $channel,
        ?string $format = "%datetime% %message%\n",
        ?string $dateFormat = 'd.m.Y H:i'
    ) {
        $dir = Application::getDocumentRoot() . '/upload/logs';

        $stream = $dir . DIRECTORY_SEPARATOR . $channel . '.log';

        $formatter = new LineFormatter( $format, $dateFormat, true, true );
        $stream = new StreamHandler( $stream );
        $stream->setFormatter( $formatter );
        $this->logger = new Logger( $channel );
        $this->logger->pushHandler( $stream );
    }

    /**
     * @param string $method
     * @param array  $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->logger->$method( ...$parameters );
    }
}
