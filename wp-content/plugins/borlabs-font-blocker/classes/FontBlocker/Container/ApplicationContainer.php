<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\Container;

final class ApplicationContainer
{
    private static $container;

    public static function get(): Container
    {
        return self::$container;
    }

    public static function init($container): void
    {
        self::$container = $container;
    }
}
