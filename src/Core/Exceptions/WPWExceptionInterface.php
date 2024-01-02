<?php


namespace WPWCore\Exceptions;

/**
 * Interface for HTTP error exceptions.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
interface WPWExceptionInterface extends \Throwable
{

    /**
     * Returns the status code.
     */
    public function getStatusCode(): int;

}
