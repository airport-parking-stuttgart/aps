<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\System\WordPressFrontendDriver;

use Borlabs\FontBlocker\System\Buffer\BufferManager;
use Borlabs\FontBlocker\System\FontBlocker\FontBlockerManager;
use Exception;

final class WordPressFrontendInit
{
    /**
     * @var \Borlabs\FontBlocker\System\Buffer\BufferManager
     */
    private $bufferManager;

    /**
     * @var \Borlabs\FontBlocker\System\FontBlocker\FontBlockerManager
     */
    private $fontBlockerManager;

    public function __construct(BufferManager $bufferManager, FontBlockerManager $fontBlockerManager)
    {
        $this->bufferManager = $bufferManager;
        $this->fontBlockerManager = $fontBlockerManager;
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
        add_action('script_loader_tag', [$this->fontBlockerManager, 'blockHandles'], 999, 3);
        add_action('style_loader_tag', [$this->fontBlockerManager, 'blockHandles'], 999, 3);
        add_action('wp_resource_hints', [$this->fontBlockerManager, 'blockPrefetch'], 999, 2);
        $this->fontBlockerManager->register();

        // TODO: not finished
        //add_action('template_redirect', [$this->bufferManager, 'startBuffering'], 19021987);
    }
}
