<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\Adapter;

use Exception;

/**
 * Class WPDB.
 */
final class WPDB
{
    public static function getInstance(): \wpdb
    {
        global $wpdb;

        return $wpdb;
    }

    public function __construct()
    {
    }

    public function __call($method, $args)
    {
        return call_user_func_array([self::getInstance(), $method], $args);
    }

    public static function __callStatic($method, $args)
    {
        return forward_static_call_array([self::getInstance(), $method], $args);
    }

    public function __clone()
    {
        throw new Exception('Cloning is not allowed.', E_USER_ERROR);
    }

    public function __get($varname)
    {
        return self::getInstance()->{$varname};
    }

    public function __wakeup(): void
    {
        throw new Exception('Unserialize is forbidden.', E_USER_ERROR);
    }
}
