<?php

namespace PhpFlo\Core\Interaction;


use PhpFlo\Common\ComponentInterface;
use PhpFlo\Common\NodeSpecInterface;

class NetworkProcess implements NetworkProcessInterface
{
    use MetadataTrait;

    /**
     * @var string $id
     */
    protected $id;

    /**
     * @var ComponentInterface $component
     */
    protected $component;

    /**
     * @var string $componentName
     */
    protected $componentName;

    /**
     * NetworkProcess constructor.
     * @param NodeSpecInterface $nodeSpec
     * @param ComponentInterface $component
     * @param string $componentName
     */
    public function __construct( NodeSpecInterface $nodeSpec, ComponentInterface $component, string $componentName )
    {
        $this->component = $component;
        $this->componentName = $componentName;
        $this->id = $nodeSpec->getId();
        $this->metadata = is_array($nodeSpec->getMetadata()) ? $nodeSpec->getMetadata() : [];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * @return ComponentInterface
     */
    public function getComponent(): ComponentInterface
    {
        return $this->component;
    }

    public function __toString(): string
    {
        return sprintf("%s(id='%s', component=%s, metadata=%s)", get_class(), $this->getId(),
            get_class($this->getComponent()), var_dump_string($this->getMetadata()) );
    }


    /**
     * @param string $newId
     */
    public function setId(string $newId)
    {
        $this->id = $newId;
    }

    /**
     * @return string
     */
    public function getComponentName(): string
    {
        return $this->componentName;
    }

}