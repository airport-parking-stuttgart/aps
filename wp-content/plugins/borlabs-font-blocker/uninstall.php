<?php

declare(strict_types=1);

if (! defined('WP_UNINSTALL_PLUGIN')) {
    die;
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
    $container->get(\Borlabs\FontBlocker\System\Uninstall\Uninstall::class)->run();
}
