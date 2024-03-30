<?php

namespace WPWCore\Exceptions;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use WPWCore\Auth\Access\AuthorizationException;
use WPWCore\Console\View\Components\BulletList;
use WPWCore\Console\View\Components\Error;
use WPWCore\Session\TokenMismatchException;
use WPWhales\Contracts\Debug\ExceptionHandler;
use WPWhales\Contracts\Support\Responsable;
use WPWCore\Database\Eloquent\ModelNotFoundException;
use WPWhales\Http\Exceptions\HttpResponseException;
use WPWhales\Http\JsonResponse;
use WPWhales\Http\Response;
use WPWhales\Support\Arr;
use WPWhales\Support\Facades\URL;
use WPWhales\Support\ViewErrorBag;
use WPWhales\Validation\ValidationException;

class Handler implements ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $e
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $e)
    {

        /**
         * TODO We'll integrate it later when we have loggin system integrated
         */

        return false;

        if ($this->shouldntReport($e)) {
            return;
        }

        if (method_exists($e, 'report')) {
            if ($e->report() !== false) {
                return;
            }
        }

        try {
            $logger = \WPWhales\app(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $e; // throw the original exception
        }

        $logger->error($e->getMessage(), ['exception' => $e]);
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param \Throwable $e
     * @return bool
     */
    public function shouldReport(Throwable $e)
    {
        return !$this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param \Throwable $e
     * @return bool
     */
    protected function shouldntReport(Throwable $e)
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \WPWhales\Http\Request $request
     * @param \Throwable $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {


        if (method_exists($e, 'render') && $response = $e->render($request)) {


            return $e->render($request);
        } elseif ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } elseif ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        } elseif ($e instanceof AuthorizationException) {
            $e = new HttpException($e->status() ?? 403, $e->getMessage(),$e);
        }elseif($e instanceof TokenMismatchException){
            $e = new HttpException(419, "CSRF Token Mismatch",$e);
        }
        elseif ($e instanceof ValidationException && $e->getResponse()) {

            //if doing ajax
            if($request->expectsJson() || wp_doing_ajax()){

                return $e->getResponse();
            }else{
                $viewBag = new ViewErrorBag();
                $viewBag->put($e->errorBag,$e->validator->getMessageBag());

                return \WPWCore\redirect()->to(\WPWCore\app("url")->previous())
                    ->withInput($request->input())
                    ->withErrors($viewBag->getBag($e->errorBag), $e->errorBag);
            }


        }



        return ($request->expectsJson() || wp_doing_ajax())
            ? $this->prepareJsonResponse($request, $e)
            : $this->prepareResponse($request, $e);
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param \WPWhales\Http\Request $request
     * @param \Throwable $e
     * @return \WPWhales\Http\JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {


        return new JsonResponse(
            $this->convertExceptionToArray($e),
            ($this->isHttpException($e) || $this->isWPWException($e)) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Convert the given exception to an array.
     *
     * @param \Throwable $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {


        //TODO replace this true with app.debug value and integrate WP_DEBUG value with app.debug
        if (\WPWCore\config('app.debug', false)) {
            return [
                'message'   => $e->getMessage(),
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => \WPWCore\Collections\collect($e->getTrace())
                    ->map(function ($trace) {
                        return Arr::except($trace, ['args']);
                    })->all(),
            ];
        }


        return [
            'message' => ($this->isHttpException($e) || $this->isWPWException($e)) ? $e->getMessage() : 'Server Error',
        ];
    }

    /**
     * Prepare a response for the given exception.
     *
     * @param \WPWhales\Http\Request $request
     * @param \Throwable $e
     * @return \WPWhales\Http\Response
     */
    protected function prepareResponse($request, Throwable $e)
    {
        //TODO replace this true with app.debug value and integrate WP_DEBUG value with app.debug
        $response = new Response(
            $this->renderExceptionWithSymfony($e, \WPWCore\config('app.debug', WP_DEBUG)),
            ($this->isHttpException($e) || $this->isWPWException($e)) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : []
        );

        $response->exception = $e;

        return $response;
    }

    /**
     * Render an exception to a string using Symfony.
     *
     * @param \Throwable $e
     * @param bool $debug
     * @return string
     */
    protected function renderExceptionWithSymfony(Throwable $e, $debug)
    {
        $renderer = new HtmlErrorRenderer($debug);

        return $renderer->render($e)->getAsString();
    }

    /**
     * Render an exception to the console.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Throwable $e
     * @return void
     */
    public function renderForConsole($output, Throwable $e)
    {
        if ($e instanceof CommandNotFoundException) {
            $message = \WPWCore\Support\str($e->getMessage())
                ->explode('.')->first();

            if (!empty($alternatives = $e->getAlternatives())) {
                $message .= '. Did you mean one of these?';

                \WPWCore\Support\with(new Error($output))
                    ->render($message);
                \WPWCore\Support\with(new BulletList($output))
                    ->render($e->getAlternatives());

                $output->writeln('');
            } else {
                \WPWCore\Support\with(new Error($output))
                    ->render($message);
            }

            return;
        }

        (new ConsoleApplication)->renderThrowable($e, $output);
    }

    /**
     * Determine if the given exception is an HTTP exception.
     *
     * @param \Throwable $e
     * @return bool
     */
    protected function isHttpException(Throwable $e)
    {
        return $e instanceof HttpExceptionInterface;
    }


    /**
     * Determine if the given exception is an WPW exception.
     *
     * @param \Throwable $e
     * @return bool
     */
    protected function isWPWException(Throwable $e)
    {
        return $e instanceof WPWExceptionInterface;
    }
}
