<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\DTO\System;

/**
 * The **OptionDTO** class is used as a typed object that is passed within the system.
 *
 * @see \Borlabs\FontBlocker\DTO\System\OptionDTO::$name
 * @see \Borlabs\FontBlocker\DTO\System\OptionDTO::$value
 * @see \Borlabs\FontBlocker\DTO\System\OptionDTO::$isGlobal
 * @see \Borlabs\FontBlocker\DTO\System\OptionDTO::$language
 * @see \Borlabs\FontBlocker\System\Option\Option
 */
final class OptionDTO
{
    /**
     * @var bool default: `false`; `true`: The option is used for all instances of a multisite network
     */
    public $isGlobal;

    /**
     * @var null|string default: `null`; `string`: The option is used for a specific language
     */
    public $language;

    /**
     * @var string The name of the option, which must match `[A-Z]+[a-zA-Z]+` when used for non-third-party options.
     *             The prefix `BorlabsFontBlocker` is set by {@see \Borlabs\FontBlocker\System\Option\Option} when used for
     *             non-third-party options.
     */
    public $name;

    /**
     * @var mixed any serializable data
     */
    public $value;

    /**
     * OptionDTO constructor.
     *
     * @param string      $name     The name of the option, which must match `[A-Z]+[a-zA-Z]+` when used for non-third-party
     *                              options. The prefix `BorlabsFontBlocker` is set by {@see \Borlabs\FontBlocker\System\Option\Option} when used for
     *                              non-third-party options.
     * @param null        $value    any serializable data
     * @param bool        $isGlobal default: `false`; `true`: The option is used for all instances of a multisite network
     * @param null|string $language default: `null`; `string`: The option is used for a specific language
     */
    public function __construct(
        string $name,
        $value = null,
        bool $isGlobal = false,
        ?string $language = null
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->isGlobal = $isGlobal;
        $this->language = $language;
    }
}
