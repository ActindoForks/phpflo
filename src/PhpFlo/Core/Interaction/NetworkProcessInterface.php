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

    public function __toString(): string;
}