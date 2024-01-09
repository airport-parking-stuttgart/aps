<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\System\Buffer;

use Exception;

final class BufferManager
{
    private $buffer = '';

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

    public function &getBuffer()
    {
        $this->buffer = ob_get_contents();

        return $this->buffer;
    }

    public function endBuffering()
    {
        ob_end_clean();
        echo $this->buffer;
        unset($this->buffer);
    }

    public function startBuffering()
    {
        ob_start();
    }
}
