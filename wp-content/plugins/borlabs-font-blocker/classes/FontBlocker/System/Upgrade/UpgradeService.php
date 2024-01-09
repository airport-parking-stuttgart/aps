<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\System\Upgrade;

use autoptimizeCache;
use Borlabs\FontBlocker\Adapter\WPDB;

final class UpgradeService
{
    private const VERSION_UPGRADES = [
    ];

    /**
     * clearCache function.
     */
    public function clearCache(): void
    {
        // Autoptimize
        if (class_exists('\autoptimizeCache')) {
            autoptimizeCache::clearall();
        }

        // Borlabs Cache
        if (class_exists('\Borlabs\Cache\Frontend\Garbage')) {
            \Borlabs\Cache\Frontend\Garbage::getInstance()->clearStylesPreCacheFiles();
            \Borlabs\Cache\Frontend\Garbage::getInstance()->clearCache();
        }

        // WP Fastest Cache
        if (function_exists('wpfc_clear_all_cache')) {
            wpfc_clear_all_cache(true);
        }

        // WP Rocket
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }

        // WP Super Cache
        if (function_exists('wp_cache_clean_cache')) {
            global $file_prefix;

            if (isset($file_prefix)) {
                wp_cache_clean_cache($file_prefix);
            }
        }

        update_option('BorlabsFontBlockerClearCache', false, 'no');
    }

    public function processUpgrade(): void
    {
        $lastVersion = get_option('BorlabsFontBlockerVersion', false);

        if (is_multisite()) {
            $allBlogs = WPDB::getInstance()->get_results(
                '
                SELECT
                    `blog_id`
                FROM
                    `' . WPDB::getInstance()->base_prefix . 'blogs`
            ',
            );
        }

        if ($lastVersion !== false) {
            foreach (self::VERSION_UPGRADES as $upgradeFunction => $version) {
                if (version_compare($lastVersion, $version, '<')) {
                    if (method_exists($this, $upgradeFunction)) {
                        // Call upgrade function
                        call_user_func([$this, $upgradeFunction]);

                        // Upgrade multisites
                        if (is_multisite() && isset($allBlogs) && is_array($allBlogs)) {
                            $originalBlogId = get_current_blog_id();

                            foreach ($allBlogs as $blogData) {
                                if ((int) $blogData->blog_id !== 1) {
                                    switch_to_blog($blogData->blog_id);
                                    call_user_func([$this, $upgradeFunction]);
                                    switch_to_blog($originalBlogId);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
