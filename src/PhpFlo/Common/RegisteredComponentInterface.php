<?php

namespace PhpFlo\Common;


use PhpFlo\Core\Interaction\PortRegistry;

interface RegisteredComponentInterface
{
    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return PortRegistry
     */
    public function inPorts(): PortRegistry;

    /**
     * @return PortRegistry
     */
    public function outPorts(): PortRegistry;

}