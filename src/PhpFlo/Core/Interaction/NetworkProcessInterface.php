<?php

namespace PhpFlo\Core\Interaction;

use PhpFlo\Common\ComponentInterface;
use PhpFlo\Common\NodeSpecInterface;

interface NetworkProcessInterface
{

    /**
     * NetworkProcess constructor.
     * @param NodeSpecInterface $nodeSpec
     * @param ComponentInterface $component
     */
    public function __construct( NodeSpecInterface $nodeSpec, ComponentInterface $component );

    /**
     * @return string
     */
    public function getId(): string;


    /**
     * @param string $newId
     */
    public function setId(string $newId);

    /**
     * @return ComponentInterface
     */
    public function getComponent(): ComponentInterface;

    /**
     * @return array
     */
    public function getMetadata(): array;

    /**
     * @param array $metadata
     */
    public function mergeMetadata(array $metadata);

    public function __toString(): string;
}