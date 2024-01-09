<?php
/*
Plugin Name: Borlabs Font Blocker
Plugin URI: https://borlabs.io/
Description: Attempts to block Google Font & Font Awesome embeddings.
Author: Borlabs GmbH
Author URI: https://borlabs.io
Version: 1.0.5
Text Domain: borlabs-font-blocker
*/

define('BORLABS_FONT_BLOCKER_VERSION', '1.0.5');
define('BORLABS_FONT_BLOCKER_BUILD', '221025');
define('BORLABS_FONT_BLOCKER_BASENAME', plugin_basename(__FILE__));
define('BORLABS_FONT_BLOCKER_SLUG', basename(BORLABS_FONT_BLOCKER_BASENAME, '.php'));
define('BORLABS_FONT_BLOCKER_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BORLABS_FONT_BLOCKER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (BORLABS_FONT_BLOCKER_BUILD === '000000' && !defined('DISABLE_WP_CRON')) {
    define('DISABLE_WP_CRON', true);
}

if (version_compare(phpversion(), '7.4', '>=')) {
    if (!class_exists('Borlabs\Autoloader')) {
        include_once plugin_dir_path(__FILE__) . 'classes/Autoloader.php';
    }

    \Borlabs\Autoloader::getInstance()->register();
    \Borlabs\Autoloader::getInstance()->addNamespace(
        'Borlabs\FontBlocker',
        realpath(plugin_dir_path(__FILE__) . '/classes/FontBlocker')
    );

    $container = new \Borlabs\FontBlocker\Container\Container;
    \Borlabs\FontBlocker\Container\ApplicationContainer::init($container);

    if ((defined('WP_CLI') && WP_CLI === true) || is_admin()) {
        $container->get(\Borlabs\FontBlocker\System\Update\UpdateService::class)->initUpdateHooks();
    }
    /* Call after upgrade process is complete */
    add_action(
        'upgrader_process_complete',
        [$container->get(\Borlabs\FontBlocker\System\Update\UpdateService::class), 'upgradeComplete'],
        10,
        2
    );

    /* Init plugin */
    if (is_admin()) {
        /* Admin */
        add_action(
            'init',
            [$container->get(\Borlabs\FontBlocker\System\WordPressAdminDriver\WordPressAdminInit::class), 'register']
        );
    }
    if (!is_admin()) {
        /* Frontend */
        add_action(
            'init',
            [$container->get(\Borlabs\FontBlocker\System\WordPressFrontendDriver\WordPressFrontendInit::class), 'register']
        );
    }
} else {
    // Fallback for very old php version
    add_action('admin_notices', function () {
        ?>
        <div class="notice notice-error">
            <p><?php
                _ex(
                    'Your PHP version is <a href="http://php.net/supported-versions.php" rel="nofollow noopener noreferrer" target="_blank">outdated</a> and not supported by Borlabs Font Blocker. Please disable Borlabs Font Blocker, upgrade to PHP 7.4 or higher, and enable Borlabs Font Blocker again. It is necessary to follow these steps in the exact order described.',
                    'Backend / Global / Alert Message',
                    'borlabs-font-blocker'
                ); ?></p>
        </div>
        <?php
    });
}
