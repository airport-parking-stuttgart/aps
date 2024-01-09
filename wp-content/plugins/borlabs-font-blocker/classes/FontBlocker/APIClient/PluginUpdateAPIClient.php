<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\APIClient;

use Borlabs\FontBlocker\HTTPClient\HTTPClient;
use Exception;

final class PluginUpdateAPIClient
{
    public const API_URL = 'https://update.borlabs.io/v2';

    /**
     * @var \Borlabs\FontBlocker\HTTPClient\HTTPClient
     */
    private $httpClient;

    public function __construct(HTTPClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function __clone()
    {
        throw new Exception('Cloning is not allowed.', E_USER_ERROR);
    }

    public function __wakeup(): void
    {
        throw new Exception('Unserialize is forbidden.', E_USER_ERROR);
    }

    /**
     * @return bool|object
     */
    public function requestLatestVersion(): ?object
    {
        $url = self::API_URL . '/latest-version/';
        $url .= defined('BORLABS_FONT_BLOCKER_DEV_BUILD') && BORLABS_FONT_BLOCKER_DEV_BUILD === true ? 'dev-' : '';
        $url .= BORLABS_FONT_BLOCKER_SLUG;

        // TODO Use HTTPClient class
        $response = wp_remote_post($url, [
            'timeout' => 45,
            'body' => [
                'backend_url' => get_site_url(),
                'debug_php_time' => date('Y-m-d H:i:s'),
                'debug_php_timestamp' => time(),
                'debug_timezone' => date_default_timezone_get(),
                'frontend_url' => get_home_url(),
                'licenseKey' => 'no-key',
                'network_url' => network_site_url(),
                'php_version' => phpversion(), // Used to distinguish between >=7.4 and <7.4 builds
                'product' => BORLABS_FONT_BLOCKER_SLUG,
                'version' => BORLABS_FONT_BLOCKER_VERSION,
            ],
        ]);

        if (is_array($response) && isset($response['body'])) {
            $body = json_decode($response['body']);

            if (isset($body->success, $body->updateInformation)) {
                return unserialize($body->updateInformation);
            }
        }

        return null;
    }

    public function requestPluginInformation(): ?object
    {
        $url = self::API_URL . '/plugin-information/';
        $url .= defined('BORLABS_FONT_BLOCKER_DEV_BUILD') && BORLABS_FONT_BLOCKER_DEV_BUILD === true ? 'dev-' : '';
        $url .= BORLABS_FONT_BLOCKER_SLUG;

        // TODO Use HTTPClient class
        $response = wp_remote_post($url, [
            'timeout' => 45,
            'body' => [
                'backend_url' => get_site_url(),
                'frontend_url' => get_home_url(),
                'language' => get_locale(),
                'licenseKey' => 'no-key',
                'network_url' => network_site_url(),
                'php_version' => phpversion(), // Used to distinguish between >=7.4 and <7.4 builds
                'product' => BORLABS_FONT_BLOCKER_SLUG,
                'version' => BORLABS_FONT_BLOCKER_VERSION,
            ],
        ]);

        if (is_array($response) && isset($response['body'])) {
            $body = json_decode($response['body']);

            if (isset($body->success, $body->pluginInformation)) {
                return unserialize($body->pluginInformation);
            }
        }

        return null;
    }
}
