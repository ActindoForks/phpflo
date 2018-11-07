<?php

namespace PhpFlo\Core\Interaction;


interface MetadataInterface
{

    /**
     * @return array
     */
    public function getMetadata(): array;

    /**
     * @param array $metadata
     */
    public function mergeMetadata(array $metadata);

}