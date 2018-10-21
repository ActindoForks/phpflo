<?php

namespace PhpFlo\Core\Interaction;


use PhpFlo\Common\ComponentInterface;
use PhpFlo\Common\NodeSpecInterface;

class NetworkProcess implements NetworkProcessInterface
{
    /**
     * @var string $id
     */
    protected $id;

    /**
     * @var ComponentInterface $component
     */
    protected $component;

    /**
     * @var array $metadata
     */
    protected $metadata;

    /**
     * NetworkProcess constructor.
     * @param NodeSpecInterface $nodeSpec
     * @param ComponentInterface $component
     */
    public function __construct( NodeSpecInterface $nodeSpec, ComponentInterface $component )
    {
        $this->component = $component;
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

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     */
    public function mergeMetadata(array $metadata)
    {
        if( !is_array($metadata) )
        {
            throw new \InvalidArgumentException('$metadata must be an array');
        }
        $this->metadata = array_merge( $this->metadata, $metadata );
        foreach( $this->metadata as $_key => $_value )
        {
            if( is_null($_value) )
            {
                unset( $this->metadata[$_key] );
            }
        }
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
}