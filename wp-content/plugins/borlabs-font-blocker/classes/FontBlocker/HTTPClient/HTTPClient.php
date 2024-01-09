<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\HTTPClient;

use Borlabs\FontBlocker\DTO\APIClient\ServiceResponseDTO;
use Borlabs\FontBlocker\Helper\Formatter;
use Borlabs\FontBlocker\Helper\HMAC;
use Borlabs\FontBlocker\Localization\HTTPClient\HTTPClientLocalizationStrings;
use Exception;
use stdClass;
use WP_Error;

final class HTTPClient implements HTTPClientInterface
{
    private $localization;

    public function __construct()
    {
        $this->localization = HTTPClientLocalizationStrings::get();
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
        ?string $salt = null
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

    private function addAuthenticationHeader(
        array $args,
        object $body,
        string $salt
    ): array {
        $args['headers'] = [
            'X-Borlabs-Font-Blocker-Auth' => HMAC::hash($body, $salt),
        ];

        return $args;
    }

    /**
     * By default, cURL sends the "Expect" header all the time which severely impacts
     * performance. Instead, we'll send it if the body is larger than 1 mb like
     * Guzzle does.
     * Source: https://gist.github.com/carlalexander/c779b473f62dcd1a4ca26fcaa637ec59.
     */
    private function addExpectHeader(
        array $args,
        object $body
    ): array {
        $bodyLength = strlen(implode('', (array) $body));
        $args['headers']['expect'] = $bodyLength > 1048576 ? '100-Continue' : '';

        return $args;
    }

    private function execute(
        string $method,
        string $url,
        object $data,
        ?string $salt = null
    ): ServiceResponseDTO {
        $args = [
            'timeout' => 45,
            'body' => (array) $data,
        ];
        $url = $this->replaceURL($url);

        if (is_string($salt)) {
            $args = $this->addAuthenticationHeader($args, $data, $salt);
        }

        $args = $this->addExpectHeader($args, $data);

        if ($method === 'POST') {
            $response = wp_remote_post($url, $args);
        } elseif ($method === 'GET') {
            $response = wp_remote_get($url, $args);
        }

        if (!isset($response)) {
            return new ServiceResponseDTO(
                false,
                'ohBoi',
                'If you get that, someone really screwed up.',
                new stdClass(),
            );
        }

        // Server error - e.g. domain not found or ssl certificate problem
        if ($response instanceof WP_Error) {
            return new ServiceResponseDTO(
                false,
                (string) $response->get_error_code(),
                $response->get_error_message(),
                $response,
                true,
            );
        }

        // Requested resource not available
        if (isset($response['response']['code']) && $response['response']['code'] !== 200) {
            return new ServiceResponseDTO(
                false,
                (string) $response['response']['code'],
                (string) $response['response']['message'],
                (object) $response,
            );
        }

        // Requested resource available
        $serviceResponse = json_decode($response['body']);

        return new ServiceResponseDTO(
            !isset($serviceResponse->error) || !$serviceResponse->error,
            $serviceResponse->errorCode ?? '',
            isset($serviceResponse->errorCode) ? $this->translateErrorCode(
                $serviceResponse->errorCode,
                $serviceResponse->message,
            ) : '',
            $serviceResponse,
        );
    }

    /**
     * Replaces the URL with the BORLABS_FONT_BLOCKER_API_URL_REPLACE constant.
     * Warning: Use the BORLABS_FONT_BLOCKER_API_URL_REPLACE constant only for development setups.
     */
    private function replaceURL(string $url): string
    {
        if (defined('BORLABS_FONT_BLOCKER_API_URL_REPLACE') && is_array(BORLABS_FONT_BLOCKER_API_URL_REPLACE)) {
            foreach (BORLABS_FONT_BLOCKER_API_URL_REPLACE as $key => $value) {
                $url = str_replace($key, $value, $url);
            }
        }

        return $url;
    }

    private function translateErrorCode(
        string $errorCode,
        string $message = ''
    ): string {
        if (isset($this->localization['alert'][$errorCode])) {
            return Formatter::interpolate($this->localization['alert'][$errorCode], ['message' => $message]);
        }

        return Formatter::interpolate($this->localization['alert']['unkown'], ['message' => $message]);
    }
}
