<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\Helper;

/**
 * Static class Formatter.
 *
 * This class contains a collection of static methods that format data.
 *
 * @see \Borlabs\FontBlocker\Helper\Formatter::timestamp() Formats a timestamp to the specified format.
 * @see \Borlabs\FontBlocker\Helper\Formatter::interpolate() Interpolates context values into the message placeholders.
 */
final class Formatter
{
    /**
     * Interpolates context values into the message placeholders.
     *
     * @param array<string, string> $context
     */
    public static function interpolate(string $message, array $context = []): string
    {
        $replace = [];

        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{{ ' . $key . ' }}'] = $val;
            }
        }

        return strtr($message, $replace);
    }

    /**
     * Formats a timestamp to the specified format.
     *
     * @param int         $timestamp  Unix timestamp
     * @param null|string $dateFormat Optional; Default: WordPress 'date_format' option; Example: Y-m-d
     * @param null|string $timeFormat Optional; Default: WordPress 'time_format' option; Example: H:i
     */
    public static function timestamp(int $timestamp, ?string $dateFormat = null, ?string $timeFormat = null): string
    {
        if (is_null($dateFormat)) {
            $dateFormat = get_option('date_format');
        }

        if (is_null($timeFormat)) {
            $timeFormat = get_option('time_format');
        }

        $dateFormat .= (isset($dateFormat) && isset($timeFormat) ? ' ' : '');
        $dateFormat .= $timeFormat;

        return date_i18n($dateFormat, $timestamp);
    }
}
