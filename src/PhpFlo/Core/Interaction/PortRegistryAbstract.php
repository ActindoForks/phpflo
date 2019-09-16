<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Patrick Prasse
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PhpFlo\Core\Interaction;

use PhpFlo\Common\PortInterface;
use PhpFlo\Common\Exception\PortException;

/**
 * Class PortRegistry
 *
 * @package PhpFlo\Core\Interaction
 * @author Patrick Prasse
 */
abstract class PortRegistryAbstract implements PortRegistryInterface
{
    /**
     * @var Port[]
     */
    protected $ports;

    /**
     * @var int
     */
    protected $position;

    public function __construct()
    {
        $this->position = 0;
        $this->ports    = [];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        $hasPort = false;

        if (array_key_exists($name, $this->ports)) {
            $hasPort = true;
        }

        return $hasPort;
    }

    /**
     * Return one or all ports.
     *
     * @param string $name
     * @return PortInterface[]|PortInterface
     * @throws PortException
     */
    public function get(string $name = '')
    {
        switch (true) {
            case ('' == $name):
                $result = $this->ports;
                break;
            case $this->has($name):
                $result = $this->ports[$name];
                break;
            default:
                throw new PortException("The port {$name} does not exist!");
        }

        return $result;
    }

    /**
     * @param string $name
     * @return PortRegistry
     */
    public function remove(string $name): PortRegistryAbstract
    {
        if ($this->has($name)) {
            $this->ports[$name] = null;
            unset($this->ports[$name]);
        }

        return $this;
    }

    /**
     * @param string $name
     * @return Port|ArrayPort
     * @throws PortException
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        $index = array_keys($this->ports);

        return $this->ports[$index[$this->position]];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        $index = array_keys($this->ports);

        return isset($index[$this->position]);
    }

    /**
     * Count elements of an object
     *
     * @return int The custom count as an integer.
     */
    public function count()
    {
        return count($this->ports);
    }
}
