<?php


use WPWhales\Support\Facades\Menu;

Menu::add("XYZ", \Tests\Menu\MenuHandlerExtendingAbstract::class, "read")->routeName("xyz");
