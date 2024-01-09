<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\HTTPClient;

use Borlabs\FontBlocker\DTO\APIClient\ServiceResponseDTO;

interface HTTPClientInterface
{
    public function __construct();

    public function get(
        string $url,
        object $data,
        ?string $salt = null
    ): ServiceResponseDTO;

    public function post(
        string $url,
        object $data,
        ?string $salt = null
    ): ServiceResponseDTO;
}
