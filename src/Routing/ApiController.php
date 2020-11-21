<?php

namespace Medvinator\BxForce\Routing;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Response;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class ApiController extends JsonController
{
    protected $authentication = null;
    protected $processableExceptions = [
        NotFoundHttpException::class,
    ];

    /**
     * Returns default pre-filters for action.
     *
     * @return array
     */
    protected function getDefaultPreFilters(): array
    {
        $filters = [];

        if ( $this->authentication && is_subclass_of( $this->authentication, ActionFilter\Base::class ) ) {
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
        $result = collect( $this->processableExceptions )->every( function ($processableException) use ($e) {
            return $e instanceof $processableException;
        } );

        if ( $result === false ) {
            return;
        }

        $this->errorCollection = new ErrorCollection;
        switch (true) {
            case $e instanceof NotFoundHttpException:
                Context::getCurrent()->getResponse()->setStatus( 404 );
        }
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
        $content = empty( $errors )
            ? json_decode( $response->getContent(), true )['data']
            : [ 'message' => (string) $errors[0] ];

        $response->setContent( $content ? json_encode( $content ) : null );
    }
}
