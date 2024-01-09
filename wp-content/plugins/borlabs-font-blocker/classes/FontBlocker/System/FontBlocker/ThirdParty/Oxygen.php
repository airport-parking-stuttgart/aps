<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\System\FontBlocker\ThirdParty;

use Borlabs\FontBlocker\System\Option\Option;
use Exception;

final class Oxygen
{
    private $option;

    public function __construct(Option $option)
    {
        $this->option = $option;
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
        $this->option->setThirdPartyOption('oxygen_vsb_disable_google_fonts', 'true');
        $this->option->setThirdPartyOption('oxygen_vsb_use_css_for_google_fonts', 'true');
    }
}
