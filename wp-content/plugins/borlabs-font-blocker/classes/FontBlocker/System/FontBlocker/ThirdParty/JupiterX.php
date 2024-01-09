<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\System\FontBlocker\ThirdParty;

use Exception;

final class JupiterX
{
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

    public function register(): void
    {
        add_action('wp_head', static function (): void {
            wp_deregister_script('jupiterx-webfont');
        }, 9999);
    }
}
