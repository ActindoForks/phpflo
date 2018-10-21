<?php

namespace PhpFlo\Core\Interaction;

use PhpFlo\Common\EdgeEndSpecInterface;

class EdgeEndSpec implements EdgeEndSpecInterface
{
    protected $nodeId;

    protected $portName;

    /**
     * EdgeEndSpec constructor.
     * @param string $nodeId
     * @param string $portName
     */
    public function __construct(string $nodeId, string $portName)
    {
        $this->nodeId = $nodeId;
        $this->portName = $portName;
    }

    /**
     * @return mixed
     */
    public function getNodeId(): string
    {
        return $this->nodeId;
    }

    /**
     * @return mixed
     */
    public function getPortName(): string
    {
        return $this->portName;
    }



}