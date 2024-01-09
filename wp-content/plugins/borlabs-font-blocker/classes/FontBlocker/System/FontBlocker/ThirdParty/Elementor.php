<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\System\FontBlocker\ThirdParty;

use Exception;

final class Elementor
{
    public static function register(): void
    {
        add_filter('elementor/frontend/print_google_fonts', '__return_false', 9999);
    }

    public function __construct()
    {
    }

    public function __clone()
    {
        throw new Exception('Cloning is not allowed.', E_USER_ERROR);
    }

    public function __wakeup(): void
    {
        throw new Exception('Unserialize is forbidden.', E_USER_ERROR);
    }
}
