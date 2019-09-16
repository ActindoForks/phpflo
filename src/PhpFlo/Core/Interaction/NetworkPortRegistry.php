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

/**
 * Class PortRegistry
 *
 * @package PhpFlo\Core\Interaction
 * @author Patrick Prasse
 */
class NetworkPortRegistry extends PortRegistryAbstract
{
    /**
     * @var array[] $metadata
     */
    protected $metadata = [];

    /**
     * @param string $publicName
     * @param PortInterface $port
     * @param array $metadata
     * @return NetworkPortRegistry
     */
    public function add(string $publicName, PortInterface $port, array $metadata = []): NetworkPortRegistry
    {
        $this->ports[$publicName] = $port;
        $this->metadata[$publicName] = $metadata;

        return $this;
    }

    /**
     * @param string $name
     * @return PortRegistryAbstract
     */
    public function remove(string $name): PortRegistryAbstract
    {
        parent::remove( $name );
        unset($this->metadata[$name]);

        return $this;
    }
}