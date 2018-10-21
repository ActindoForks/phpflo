<?php

namespace PhpFlo\Common;


interface NodeSpecInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getComponent(): string;

    /**
     * @return array
     */
    public function getMetadata(): array;
}