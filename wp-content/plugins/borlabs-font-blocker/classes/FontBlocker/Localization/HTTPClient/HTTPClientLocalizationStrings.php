<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\Localization\HTTPClient;

/**
 * The **RequestLocalizationStrings** class contains various localized strings.
 *
 * @see \Borlabs\FontBlocker\Localization\HTTPClient\HTTPClientLocalizationStrings::get()
 */
final class HTTPClientLocalizationStrings
{
    /**
     * @return array<array<string>>
     */
    public static function get(): array
    {
        return [
            // Alert Messagess
            'alert' => [
                'unkown' => _x(
                    'An error occurred. Please contact the support. {{ message }}',
                    'Backend / API / Alert Message',
                    'borlabs-font-blocker',
                ),
                'validateHash' => _x(
                    'The request to the API could not be validated. {{ message }}',
                    'Backend / API / Alert Message',
                    'borlabs-font-blocker',
                ),
            ],
        ];
    }
}
