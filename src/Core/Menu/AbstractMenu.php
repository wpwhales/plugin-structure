<?php

namespace WPWCore\Menu;

use WPWCore\View\View;

abstract class AbstractMenu
{

    /**
     * Render the menu HTML.
     *
     * @return string
     */
    abstract protected function render():View;


    /**
     * Print the rendered menu HTML.
     */
    public function print()
    {
        echo $this->render();
    }

}