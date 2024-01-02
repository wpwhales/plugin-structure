<?php

namespace WPWCore\Concerns;

use ErrorException;
use Exception;
use WPWhales\Contracts\Debug\ExceptionHandler;
use WPWhales\Log\LogManager;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

use WPWhales\Exceptions\Handler;
trait RegistersExceptionHandlers
{

    /**
     * Send the exception to the handler and return the response.
     *
     * @param \Throwable $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendExceptionToHandler(Throwable $e)
    {
        $handler = $this->resolveExceptionHandler();

        $handler->report($e);

        return $handler->render($this->make('request'), $e);
    }

    /**
     * Get the exception handler from the container.
     *
     * @return \WPWhales\Contracts\Debug\ExceptionHandler
     */
    protected function resolveExceptionHandler()
    {
        if ($this->bound(ExceptionHandler::class)) {
            return $this->make(ExceptionHandler::class);
        } else {
            return $this->make(Handler::class);
        }
    }
}
