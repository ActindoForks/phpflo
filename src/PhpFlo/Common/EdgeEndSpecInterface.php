<?php

namespace PhpFlo\Common;


interface EdgeEndSpecInterface
{
    /**
     * @return string
     */
    public function getNodeId(): string;

    /**
     * @return string
     */
    public function getPortName(): string;
}