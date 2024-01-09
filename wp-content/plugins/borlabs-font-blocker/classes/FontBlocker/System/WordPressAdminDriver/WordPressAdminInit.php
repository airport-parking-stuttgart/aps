<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\System\WordPressAdminDriver;

use Exception;

final class WordPressAdminInit
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
        add_filter('upload_mimes', function ($mimes) {
            $mimes['eot'] = 'application/vnd.ms-fontobject';
            $mimes['svg'] = 'image/svg+xml';
            $mimes['woff'] = 'application/font-woff'; // font/woff doesn't work
            $mimes['woff2'] = 'application/font-woff2'; // font/woff2 doesn't work

            return $mimes;
        });
    }
}
