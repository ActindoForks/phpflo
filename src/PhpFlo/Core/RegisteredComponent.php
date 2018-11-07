<?php

namespace PhpFlo\Core;

use PhpFlo\Common\ComponentInterface;
use PhpFlo\Common\RegisteredComponentInterface;
use PhpFlo\Core\Interaction\PortRegistry;

class RegisteredComponent implements RegisteredComponentInterface
{
    /**
     * @var PortRegistry
     */
    private $inPorts = null;

    /**
     * @var PortRegistry
     */
    private $outPorts = null;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $icon
     */
    private $icon = '';


    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @return PortRegistry
     */
    public function inPorts(): PortRegistry
    {
        if (null === $this->inPorts) {
            $this->inPorts = new PortRegistry();
        }

        return $this->inPorts;
    }

    /**
     * @return PortRegistry
     */
    public function outPorts(): PortRegistry
    {
        if (null === $this->outPorts) {
            $this->outPorts = new PortRegistry();
        }

        return $this->outPorts;
    }

    /**
     * @param ComponentInterface $component
     * @return RegisteredComponent
     */
    public static function fromComponent( ComponentInterface $component, string $name )
    {
        $inst = new self();
        $inst->name = $name;
        $inst->description = $component->getDescription();
        $inst->inPorts = clone $component->inPorts();
        $inst->outPorts = clone $component->outPorts();
        return $inst;
    }

    protected function __construct()
    {
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }


}