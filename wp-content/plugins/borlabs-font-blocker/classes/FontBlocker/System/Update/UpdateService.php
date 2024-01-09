<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\System\Update;

use Borlabs\FontBlocker\APIClient\PluginUpdateAPIClient;
use Borlabs\FontBlocker\System\Upgrade\UpgradeService;
use stdClass;

final class UpdateService
{
    /**
     * @var \Borlabs\FontBlocker\APIClient\PluginUpdateAPIClient
     */
    private $pluginUpdateAPIClient;

    /**
     * @var \Borlabs\FontBlocker\System\Upgrade\UpgradeService
     */
    private $upgradeService;

    public function __construct(PluginUpdateAPIClient $pluginUpdateAPIClient, UpgradeService $upgradeService)
    {
        $this->pluginUpdateAPIClient = $pluginUpdateAPIClient;
        $this->upgradeService = $upgradeService;
    }

    /**
     * handlePluginAPI function.
     *
     * @param mixed  $result Default is false
     * @param string $action Type of information
     * @param object $args   Plugin API arguments
     */
    public function handlePluginAPI($result, string $action, object $args)
    {
        if (isset($action) && $action === 'plugin_information' && isset($args->slug)) {
            if ($args->slug === BORLABS_FONT_BLOCKER_SLUG) {
                // Return alternative API URL
                $result = $this->pluginUpdateAPIClient->requestPluginInformation();
            }
        }

        return $result;
    }

    /**
     * handleTransientUpdatePlugins function.
     *
     * @param mixed $transient
     */
    public function handleTransientUpdatePlugins($transient)
    {
        // If info is already available
        if (isset($transient->response[BORLABS_FONT_BLOCKER_BASENAME])) {
            return $transient;
        }

        // Check for updates
        $updateInformation = $this->pluginUpdateAPIClient->requestLatestVersion();

        if ($updateInformation !== null) {
            if (version_compare(BORLABS_FONT_BLOCKER_VERSION, $updateInformation->new_version, '<')) {
                // $transient can be null if third party plugins force a plugin refresh and kill the object
                if (!is_object($transient) && !isset($transient->response)) {
                    $transient = new stdClass();
                    $transient->response = [];
                }
                $transient->response[BORLABS_FONT_BLOCKER_BASENAME] = $updateInformation;
            }
        }

        return $transient;
    }

    public function initUpdateHooks(): void
    {
        // Overwrite API URL when request infos about Borlabs Font Blocker
        // Changed priority to avoid a conflict when third-party-devs have a broken implementation for their plugin_information routine
        add_action('plugins_api', [$this, 'handlePluginAPI'], 9001, 3);

        // Register Hook for checking for updates
        add_filter('pre_set_site_transient_update_plugins', [$this, 'handleTransientUpdatePlugins']);
    }

    /**
     * @param WP_Upgrader $upgraderObject
     * @param array       $options
     */
    public function upgradeComplete($upgraderObject, $options): void
    {
        if ($options['action'] === 'update' && $options['type'] === 'plugin' && isset($options['plugins'])) {
            // Check if this plugin was updated
            if (in_array(BORLABS_FONT_BLOCKER_BASENAME, $options['plugins'], true)) {
                $this->upgradeService->processUpgrade();
            }
        }
    }
}
