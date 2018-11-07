<?php

namespace PhpFlo\Core\Interaction;


trait MetadataTrait
{
    /**
     * @var array $metadata
     */
    protected $metadata;

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

}