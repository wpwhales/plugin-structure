<?php


namespace WPWCore\Exceptions;

/**
 * Interface for HTTP error exceptions.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class NotADefinedRouteException extends \Exception implements WPWExceptionInterface
{


    /**
     * Returns the status code.
     */
    public function getStatusCode(): int
    {


        return 404;
    }
}
