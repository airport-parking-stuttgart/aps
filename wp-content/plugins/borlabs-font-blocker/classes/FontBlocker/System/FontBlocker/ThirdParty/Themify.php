<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\System\FontBlocker\ThirdParty;

use Exception;

final class Themify
{
    public static function register(): void
    {
        add_action('after_setup_theme', static function (): void {
            if (defined('THEMIFY_GOOGLE_FONTS') === false) {
                define('THEMIFY_GOOGLE_FONTS', false);
            }
        }, 9999);
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
