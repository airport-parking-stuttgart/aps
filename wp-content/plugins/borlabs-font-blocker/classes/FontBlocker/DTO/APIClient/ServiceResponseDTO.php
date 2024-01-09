<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\DTO\APIClient;

/**
 * The **ServiceResponseDTO** class is used as a typed object that is passed within the system.
 *
 * It contains the status and data of the response from a request to the Borlabs servers.
 *
 * @see \Borlabs\FontBlocker\DTO\APIClient\ServiceResponseDTO::$success
 * @see \Borlabs\FontBlocker\DTO\APIClient\ServiceResponseDTO::$messageCode
 * @see \Borlabs\FontBlocker\DTO\APIClient\ServiceResponseDTO::$message
 * @see \Borlabs\FontBlocker\DTO\APIClient\ServiceResponseDTO::$data
 * @see \Borlabs\FontBlocker\DTO\APIClient\ServiceResponseDTO::$serviceError
 */
final class ServiceResponseDTO
{
    /**
     * @var object service response data
     */
    public $data;

    /**
     * @var string status message of the request
     */
    public $message;

    /**
     * @var string a code that can sometimes be translated into a localized message
     */
    public $messageCode;

    /**
     * @var bool the requested server is not available or the local server is not able to make a connection
     */
    public $serviceError;

    /**
     * @var bool if request was successful the value is true
     */
    public $success;

    /**
     * ServiceResponseDTO constructor.
     *
     * @param bool   $success      if request was successful the value is true
     * @param string $messageCode  a code that can sometimes be translated into a localized message
     * @param string $message      status message of the request
     * @param object $data         service response data
     * @param bool   $serviceError the requested server is not available or the local server is not able to make a
     *                             connection
     */
    public function __construct(
        bool $success,
        string $messageCode,
        string $message,
        object $data,
        bool $serviceError = false
    ) {
        $this->success = $success;
        $this->messageCode = $messageCode;
        $this->message = $message;
        $this->data = $data;
        $this->serviceError = $serviceError;
    }
}
