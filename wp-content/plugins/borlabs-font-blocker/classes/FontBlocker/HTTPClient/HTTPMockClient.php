<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\HTTPClient;

use Borlabs\FontBlocker\DTO\APIClient\ServiceResponseDTO;
use Exception;

final class HTTPMockClient implements HTTPClientInterface
{
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

    public function get(
        string $url,
        object $data,
        ?string $salt = null,
    ): ServiceResponseDTO {
        return $this->execute('GET', $url, $data, $salt);
    }

    public function post(
        string $url,
        object $data,
        ?string $salt = null
    ): ServiceResponseDTO {
        return $this->execute('POST', $url, $data, $salt);
    }

    private function execute(
        string $method,
        string $url,
        object $data,
        ?string $salt = null
    ): ServiceResponseDTO {
        return new ServiceResponseDTO(
            false,
            (string) 'Hello',
            (string) 'from the other side',
            (object) null,
        );
    }
}
