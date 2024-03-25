<?php

namespace WPWCore\Assets\Contracts;

interface Bundle
{
    public function css();

    public function js();

    public function runtime();
}
