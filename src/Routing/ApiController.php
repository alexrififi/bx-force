<?php

namespace Medvinator\BxForce\Routing;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\FallbackActionInterface;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Main\Response;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class ApiController extends JsonController implements FallbackActionInterface
{
    protected $authentication = null;
    protected static $modules = [];

    public function __construct(Request $request = null)
    {
        foreach (self::$modules as $module) {
            Loader::requireModule($module);
        }

        parent::__construct($request);
    }

    /**
     * Returns default pre-filters for action.
     *
     * @return array
     */
    protected function getDefaultPreFilters(): array
    {
        $filters = [];

        if ($this->authentication && is_subclass_of($this->authentication, ActionFilter\Base::class)) {
            $filters[] = new $this->authentication;
        }

        return $filters;
    }

    /**
     * Runs processing exception.
     *
     * @param Exception $e Exception.
     * @return void
     */
    protected function runProcessingException(Exception $e)
    {
        if (! $e instanceof HttpException) {
            return;
        }

        Context::getCurrent()->getResponse()->setStatus($e->getStatusCode());
        $this->errorCollection = new ErrorCollection;
        $this->addError(new Error($e->getMessage()));
    }

    /**
     * Finalizes response.
     * The method will be invoked when HttpApplication will be ready to send response to client.
     * It's a final place where Controller can interact with response.
     *
     * @param Response $response
     * @return void
     * @throws ArgumentTypeException
     */
    public function finalizeResponse(Response $response): void
    {
        $errors = $response->getErrors();
        $content = empty($errors)
            ? json_decode($response->getContent(), true)['data']
            : ['message' => (string)$errors[0]];

        $response->setContent($content ? json_encode($content) : null);
    }

    /**
     * @param string $actionName
     * @return mixed
     */
    public function fallbackAction($actionName)
    {
        return $this->$actionName();
    }
}
