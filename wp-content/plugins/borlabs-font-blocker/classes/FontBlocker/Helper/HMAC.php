<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\Helper;

/**
 * Static class HMAC.
 *
 * This class contains methods to validate data and generate a key hashed value using the HMAC method.
 *
 * @see https://en.wikipedia.org/wiki/HMAC
 * @see \Borlabs\FontBlocker\Helper\HMAC::hash() Generate a key hashed value using the HMAC method.
 * @see \Borlabs\FontBlocker\Helper\HMAC::isValid() Validates the data against the hash.
 */
final class HMAC
{
    /**
     * Generate a key hashed value using the HMAC method.
     */
    public static function hash(object $data, string $salt): string
    {
        $data = json_encode($data);

        return hash_hmac('sha256', $data, $salt);
    }

    /**
     * Validates the data against the hash.
     */
    public static function isValid(object $data, string $salt, string $hash): bool
    {
        $is_valid = false;
        $data = json_encode($data);
        $data_hash = hash_hmac('sha256', $data, $salt);

        if ($data_hash === $hash) {
            $is_valid = true;
        }

        return $is_valid;
    }
}
