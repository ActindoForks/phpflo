<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PhpFlo\Core\Interaction;

use PhpFlo\Common\ComponentInterface;
use PhpFlo\Common\Exception\PortException;

/**
 * Class PortRegistry
 *
 * @package PhpFlo\Core\Interaction
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class PortRegistry extends PortRegistryAbstract
{
    /**
     * @var ComponentInterface $component
     */
    protected $component;


    public function __construct( ComponentInterface $component )
    {
        $this->component = $component;
        parent::__construct();
    }



    /**
     * @param string $name
     * @param array $attributes
     * @return PortRegistry
     * @throws PortException
     */
    public function add(string $name, array $attributes): PortRegistry
    {
        switch (true) {
            case (!$this->has($name) && (isset($attributes['addressable']) && false !== $attributes['addressable'])):
                $this->ports[$name] = new ArrayPort(
                    $name,
                    $attributes
                );
                break;
            case (!$this->has($name)):
                $this->ports[$name] = new Port(
                    $name,
                    $attributes
                );
                break;
            default:
                throw new PortException("The port {$name} already exists!");
        }

        return $this;
    }

    /**
     * @return ComponentInterface
     */
    public function getComponent(): ComponentInterface
    {
        return $this->component;
    }


}
