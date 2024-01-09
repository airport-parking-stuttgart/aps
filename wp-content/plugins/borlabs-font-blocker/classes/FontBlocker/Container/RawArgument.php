<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\Container;

/**
 * Class RawArgument.
 *
 * The **RawArgument** class is a wrapper for scalar parameters,
 * {@see \Borlabs\FontBlocker\Container\ContainerService}.
 */
final class RawArgument
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * RawArgument constructor.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
