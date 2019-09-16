<?php

namespace PhpFlo\Common;


use PhpFlo\Core\Interaction\PortRegistry;
use PhpFlo\Core\Interaction\PortRegistryInterface;

interface RegisteredComponentInterface
{
    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return PortRegistry
     */
    public function inPorts(): PortRegistryInterface;

    /**
     * @return PortRegistry
     */
    public function outPorts(): PortRegistryInterface;
}