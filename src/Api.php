<?php

namespace Medvinator\BxForce;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Response\Json;
use Bitrix\Main\HttpRequest;
use FastRoute\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Medvinator\BxForce\Support\Config;
use function FastRoute\simpleDispatcher;

class Api
{
    /**
     * @var Collection
     */
    protected $module;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var Json
     */
    protected $response;

    /**
     * @var callable
     */
    protected $routes;

    /**
     * @return static
     */
    public static function make()
    {
        return new static;
    }

    public function handleByModule(string $module): self
    {
        $this->module = Str::of($module)
            ->replace('.', ':')
            ->explode(':');

        return $this;
    }

    public function withRoutes(callable $routes): self
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function run(): void
    {
        $this->request = Context::getCurrent()->getRequest();
        $this->response = new Json;

        $routeInfo = simpleDispatcher($this->routes)->dispatch($this->request->getRequestMethod(), $this->request->getRequestedPage());
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $this->response->setStatus(404);
                $this->addError('Неверный эндпоинт');
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = implode(',', $routeInfo[1]);
                $this->response->setStatus(405);
                $this->response->addHeader('Allow', $allowedMethods);
                $this->addError('Метод ' . $this->request->getRequestMethod() . ' не разрешён. Для данного эндпоинта разрешается: ' . $allowedMethods);
                break;
            case Dispatcher::FOUND:
                [, $handler, $vars] = $routeInfo;
                $this->runController($handler, $vars);
        }

        $this->response->send();
    }

    /**
     * @param string $message
     */
    protected function addError(string $message): void
    {
        $this->response->setData([
            'message' => $message,
        ]);
    }

    /**
     * @param array|callable $handler
     * @param array          $vars
     */
    protected function runController($handler, array $vars): void
    {
        $controllersConfig = Configuration::getInstance($this->module->implode('.'));
        $controllerBaseNamespace = collect($controllersConfig['controllers']['namespaces'])->search('api');

        if (is_string($handler)) {
            $handler = [$handler, '__invoke'];
        }

        $controller = str_replace($controllerBaseNamespace . '\\', '', $handler[0]);
        $method = $handler[1];

        $this->request->modifyByQueryString("action={$this->module->implode( ':' )}.api.{$controller}.{$method}");
        $this->request->getPostList()->set($vars);

        Application::getInstance()->run();
    }
}
