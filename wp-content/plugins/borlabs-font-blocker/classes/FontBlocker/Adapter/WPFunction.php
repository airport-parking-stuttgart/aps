<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\Adapter;

use Borlabs\FontBlocker\Container\Container;

final class WPFunction
{
    /**
     * @var \Borlabs\FontBlocker\Container\Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * This method is a wrapper for the WordPress function `delete_site_option`. This method is used by
     * {@see \Borlabs\FontBlocker\System\Option\Option}.
     *
     * @param string $option name of the option
     */
    public function deleteGlobalOption(string $option): bool
    {
        return delete_site_option($option);
    }

    /**
     * This method is a wrapper for the WordPress function `delete_option`. This method is used by
     * {@see \Borlabs\FontBlocker\System\Option\Option}.
     *
     * @param string $option name of the option
     */
    public function deleteOption(string $option): bool
    {
        return delete_option($option);
    }

    /**
     * This method is a wrapper for the WordPress function `get_option`. This method is used by
     * {@see \Borlabs\FontBlocker\System\Option\Option}.
     *
     * @param string $option  name of the option
     * @param mixed  $default optional; Default: `false`; Default value if the option does not exist
     *
     * @return false|mixed
     */
    public function getOption(string $option, $default = false)
    {
        return get_option($option, $default);
    }

    /**
     * This method is a wrapper for the WordPress function `get_site_option`. This method is used by
     * {@see \Borlabs\FontBlocker\System\Option\Option}.
     *
     * @param string $option  name of the option
     * @param mixed  $default optional; Default: `false`; Default value if the option does not exist
     *
     * @return false|mixed
     */
    public function getSiteOption(string $option, $default = false)
    {
        return get_site_option($option, $default);
    }

    /**
     * This method is a wrapper for the WordPress function `update_option`. This method is used by
     * {@see \Borlabs\FontBlocker\System\Option\Option}.
     *
     * @param string $option   name of the option
     * @param mixed  $value    any serializable data
     * @param bool   $autoload optional; Default: `false`; `true`: The option is loaded when WordPress starts up
     */
    public function updateOption(string $option, $value, bool $autoload = false): bool
    {
        return update_option($option, $value, $autoload);
    }

    /**
     * This method is a wrapper for the WordPress function `update_site_option`. This method is used by
     * {@see \Borlabs\FontBlocker\System\Option\Option}.
     *
     * @param string $option name of the option
     * @param mixed  $value  any serializable data
     */
    public function updateSiteOption(string $option, $value): bool
    {
        return update_site_option($option, $value);
    }
}
