<?php

declare(strict_types=1);

namespace Borlabs\FontBlocker\Container;

/**
 * Describes the PSR-11 interface of a container.
 */
interface ContainerInterface
{
    /**
     * This method finds an entry by its identifier and returns it.
     *
     * @param string $id identifier of the entry to search
     *
     * @throws ContainerExceptionInterface general error
     * @throws NotFoundExceptionInterface  no entry was found for the id
     *
     * @return mixed entry
     */
    public function get(string $id);

    /**
     * Returns true if the container has an entry for the given id, false otherwise.
     *
     * @param string $id identifier of the entry to search
     */
    public function has(string $id): bool;
}
