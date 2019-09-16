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
namespace PhpFlo\Common;

use PhpFlo\Common\Exception\EdgeDoesNotExistException;
use PhpFlo\Common\Exception\FlowException;
use PhpFlo\Common\Exception\InvalidDefinitionException;
use PhpFlo\Common\Exception\NodeDoesNotExistException;
use PhpFlo\Core\Graph;
use PhpFlo\Core\Interaction\EdgeEndSpec;
use PhpFlo\Core\Interaction\NetworkProcessInterface;

/**
 * Interface NetworkInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface NetworkInterface extends HookableNetworkInterface
{
    const SOURCE = 'from';
    const TARGET = 'to';
    const NODE_ID = 'id';
    const METADATA = 'metadata';
    const COMPONENT = 'component';
    const PROCESS = 'process';
    const DATA = 'data';
    const NODE = 'node';
    const PORT = 'port';
    const CONNECT = 'connect';
    const DISCONNECT = 'disconnect';
    const SHUTDOWN = 'shutdown';
    const DETACH = 'detach';
    const CONNECTION_SOURCE = 'src';
    const CONNECTION_TARGET = 'tgt';
    const EVENT_ADD = 'add.node';
    const EVENT_REMOVE = 'remove.node';
    const EVENT_ADD_EDGE = 'add.edge';
    const EVENT_REMOVE_EDGE = 'remove.edge';
    const BEGIN_GROUP = 'begin.group';
    const END_GROUP = 'end.group';

    const INITIAL_DATA = 'initial_data';

    /**
     * @param EdgeEndSpecInterface $source
     * @param EdgeEndSpecInterface $target
     * @param array|null $metadata
     * @return NetworkInterface
     */
    public function addEdge(EdgeEndSpecInterface $source, EdgeEndSpecInterface $target, array $metadata=null): NetworkInterface;

    /**
     * @param EdgeEndSpecInterface $source
     * @param EdgeEndSpecInterface $target
     * @param array $metadata
     * @return NetworkInterface
     * @throws EdgeDoesNotExistException
     */
    public function changeEdge(EdgeEndSpecInterface $source, EdgeEndSpecInterface $target, array $metadata = []): NetworkInterface;

    /**
     * @param NodeSpecInterface $node
     * @return NetworkInterface
     * @throws InvalidDefinitionException
     */
    public function addNode(NodeSpecInterface $node): NetworkInterface;

    /**
     * @param string $from
     * @param string $to
     * @throws NodeDoesNotExistException
     */
    public function renameNode(string $from, string $to);

    /**
     * Add a flow definition as Graph object or definition file/string
     * and initialize the network processes/connections
     *
     * @param mixed $graph
     * @return NetworkInterface
     * @throws InvalidDefinitionException
     */
    public function boot($graph): NetworkInterface;

    /**
     * @return array
     */
    public function serializeJson();

    /**
     * @param string $id
     * @return NetworkProcessInterface
     * @throws NodeDoesNotExistException
     */
    public function getNode(string $id): NetworkProcessInterface;

    /**
     * @param EdgeEndSpecInterface|null $source
     * @param EdgeEndSpecInterface|null $target
     * @return NetworkInterface
     */
    public function removeEdge(EdgeEndSpecInterface $source=null, EdgeEndSpecInterface $target=null): NetworkInterface;

    /**
     * @param array $node
     * @return NetworkInterface
     */
    public function removeNode(string $id): NetworkInterface;

    /**
     * @param mixed $data
     * @param EdgeEndSpecInterface $target
     * @return NetworkInterface
     * @throws InvalidDefinitionException
     * @throws NodeDoesNotExistException
     * @throws \PhpFlo\Common\Exception\PortException
     */
    public function addInitial($data, EdgeEndSpecInterface $target): NetworkInterface;

    /**
     * Add initialization data
     *
     * @param mixed $data
     * @param string $node
     * @param string $port
     * @return NetworkInterface
     * @throws FlowException
     */
    public function run($data, string $node, string $port): NetworkInterface;

    /**
     * Cleanup network state after runs.
     *
     * @return NetworkInterface
     */
    public function shutdown(): NetworkInterface;

    /**
     * @return bool|\DateInterval
     */
    public function uptime();

    /**
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * @return bool
     */
    public function isRunning(): bool;

    /**
     * @return bool|string
     */
    public function getStartupTime();

    /**
     * @param EdgeEndSpecInterface $target
     * @return mixed
     */
    public function getInitial(EdgeEndSpecInterface $target);

    /**
     * @param EdgeEndSpecInterface $target
     */
    public function removeInitial(EdgeEndSpecInterface $target);

    /**
     * Start network
     */
    public function startup();

    /**
     * @param bool $enable
     */
    public function setDebug( bool $enable );

    /**
     * @return bool
     */
    public function isDebug(): bool;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $public
     * @param EdgeEndSpecInterface $tgt
     * @param array $metadata
     * @return void
     */
    public function addInPort( string $public, EdgeEndSpecInterface $tgt, array $metadata = [] );

    /**
     * @param string $public
     * @throws \PhpFlo\Common\Exception\PortException
     */
    public function removeInPort( string $public );

    /**
     * @param string $public
     * @param EdgeEndSpecInterface $src
     * @param array $metadata
     * @return void
     */
    public function addOutPort( string $public, EdgeEndSpecInterface $src, array $metadata = [] );

    /**
     * @param string $public
     * @throws \PhpFlo\Common\Exception\PortException
     */
    public function removeOutPort( string $public );

    /**
     * @param string $nodeId
     * @return EdgeEndSpec[][]
     * @throws NodeDoesNotExistException
     */
    public function findEdgesByNode( string $nodeId );

}
