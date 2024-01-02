<?php


namespace WPWCore\Exceptions;

/**
 * Interface for HTTP error exceptions.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class WPWException extends \Exception implements WPWExceptionInterface
{


    /**
     * Returns the status code.
     */
    public function getStatusCode(): int
    {
        $code = $this->getCode();
        if(empty($code)){
            $code = 500;
        }

        return $code;
    }
}
