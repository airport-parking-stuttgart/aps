<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\System\FontBlocker\ThirdParty;

use Exception;

final class Mailpoet
{
    public static function register(): void
    {
        add_filter('mailpoet_display_custom_fonts', static function (): bool {
            return false;
        });
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
