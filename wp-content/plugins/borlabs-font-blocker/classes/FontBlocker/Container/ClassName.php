<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\Container;

/**
 * Class ClassName.
 *
 * The **ClassName** class is a wrapper for class names,
 * {@see \Borlabs\FontBlocker\Container\ContainerService}.
 */
final class ClassName
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
