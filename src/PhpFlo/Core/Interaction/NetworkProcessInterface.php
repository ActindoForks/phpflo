<?php

namespace PhpFlo\Core\Interaction;

use PhpFlo\Common\ComponentInterface;
use PhpFlo\Common\NodeSpecInterface;

interface NetworkProcessInterface extends MetadataInterface
{

    /**
     * NetworkProcess constructor.
     * @param NodeSpecInterface $nodeSpec
     * @param ComponentInterface $component
     * @param string $componentName
     */
    public function __construct( NodeSpecInterface $nodeSpec, ComponentInterface $component, string $componentName );

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

    public function __toString(): string;

    public function getComponentName(): string;
}