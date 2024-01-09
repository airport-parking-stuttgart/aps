<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\System\FontBlocker;

use Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Avada;
use Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Divi;
use Borlabs\FontBlocker\System\FontBlocker\ThirdParty\DownloadManager;
use Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Elementor;
use Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Enfold;
use Borlabs\FontBlocker\System\FontBlocker\ThirdParty\JupiterX;
use Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Mailpoet;
use Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Oxygen;
use Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Themify;
use Exception;

final class FontBlockerManager
{
    /**
     * @var \Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Avada
     */
    private $avada;

    /**
     * @var \Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Divi
     */
    private $divi;

    /**
     * @var \Borlabs\FontBlocker\System\FontBlocker\ThirdParty\DownloadManager
     */
    private $downloadManager;

    /**
     * @var \Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Elementor
     */
    private $elementor;

    /**
     * @var \Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Enfold
     */
    private $enfold;

    /**
     * @var \Borlabs\FontBlocker\System\FontBlocker\ThirdParty\JupiterX
     */
    private $jupiterX;

    /**
     * @var \Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Mailpoet
     */
    private $mailpoet;

    /**
     * @var \Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Oxygen
     */
    private $oxygen;

    /**
     * @var \Borlabs\FontBlocker\System\FontBlocker\ThirdParty\Themify
     */
    private $themify;

    public function __construct(Avada $avada, Divi $divi, DownloadManager $downloadManager, Elementor $elementor, Enfold $enfold, JupiterX $jupiterX, Mailpoet $mailpoet, Oxygen $oxygen, Themify $themify)
    {
        $this->avada = $avada;
        $this->divi = $divi;
        $this->downloadManager = $downloadManager;
        $this->elementor = $elementor;
        $this->enfold = $enfold;
        $this->jupiterX = $jupiterX;
        $this->mailpoet = $mailpoet;
        $this->oxygen = $oxygen;
        $this->themify = $themify;
    }

    public function __clone()
    {
        throw new Exception('Cloning is not allowed.', E_USER_ERROR);
    }

    public function __wakeup(): void
    {
        throw new Exception('Unserialize is forbidden.', E_USER_ERROR);
    }

    public function blockHandles($tag, $handle, $src): string
    {
        if ($this->isFontProviderHost($src) === true) {
            return str_replace('href=', 'data-borlabs-font-blocker-href=', $tag);
        }

        return $tag;
    }

    public function blockPrefetch($urls): array
    {
        $newURLs = [];

        foreach ($urls as $urlData) {
            if (isset($urlData['href']) && $this->isFontProviderHost($urlData['href']) === false) {
                $newURLs[] = $urlData['href'];
            }
        }

        return $newURLs;
    }

    public function register(): void
    {
        $this->avada->register();
        $this->divi->register();
        $this->downloadManager->register();
        $this->elementor->register();
        $this->enfold->register();
        $this->jupiterX->register();
        $this->oxygen->register();
        $this->themify->register();
        $this->mailpoet->register();
    }

    private function isFontProviderHost($url): bool
    {
        if (strpos($url, 'use.fontawesome.com') !== false) {
            return true;
        }

        if (strpos($url, 'fonts.googleapis.com') !== false) {
            return true;
        }

        if (strpos($url, 'fonts.gstatic.com') !== false) {
            return true;
        }

        return strpos($url, 'ajax.googleapis.com') !== false && strpos($url, 'webfont.js') !== false;
    }
}
