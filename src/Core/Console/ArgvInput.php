<?php


namespace WPWCore\Console;

use Symfony\Component\Console\Input\ArgvInput as BaseArgvInput;
use Symfony\Component\Console\Input\InputDefinition;

class ArgvInput extends BaseArgvInput
{


    public function __construct(array $argv = null, InputDefinition $definition = null)
    {

        $argv ??= $_SERVER['argv'] ?? [];


        // strip the application name
        // it will also strip the WPWCORE command base
        array_shift($argv);


        parent::__construct($argv, $definition);
    }

}
