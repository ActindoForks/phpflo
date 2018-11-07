<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PhpFlo\Core;

use PhpFlo\Common\ComponentBuilderInterface;
use PhpFlo\Common\ComponentInterface;
use PhpFlo\Common\EdgeEndSpecInterface;
use PhpFlo\Common\Exception\EdgeDoesNotExistException;
use PhpFlo\Common\Exception\NodeDoesNotExistException;
use PhpFlo\Common\NetworkInterface;
use PhpFlo\Common\NodeSpecInterface;
use PhpFlo\Common\PortInterface;
use PhpFlo\Common\SocketInterface;
use PhpFlo\Common\Exception\FlowException;
use PhpFlo\Common\Exception\IncompatibleDatatypeException;
use PhpFlo\Common\Exception\InvalidDefinitionException;
use PhpFlo\Common\Exception\InvalidTypeException;
use PhpFlo\Core\Interaction\EdgeEndSpec;
use PhpFlo\Core\Interaction\InternalSocket;
use PhpFlo\Core\Interaction\NetworkProcess;
use PhpFlo\Core\Interaction\NetworkProcessInterface;
use PhpFlo\Core\Interaction\Port;
use PhpFlo\Core\Interaction\PortRegistry;

/**
 * Builds the concrete network based on graph.
 *
 * @package PhpFlo\Core
 * @author Henri Bergius <henri.bergius@iki.fi>
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class Network implements NetworkInterface, ComponentInterface
{
    use HookableNetworkTrait;
    use ComponentTrait {
        shutdown as componentTraitShutdown;
    }

    /**
     * @var NetworkProcessInterface[]
     */
    private $processes;

    /**
     * @var SocketInterface[]
     */
    private $connections;

    /**
     * @var Graph
     */
    private $graph;

    /**
     * @var \DateTime
     */
    private $startupDate;

    /**
     * @var ComponentBuilderInterface
     */
    private $builder;

    /**
     * @var bool $debug
     */
    private $debug = false;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $description
     */
    private $description = '';

    /**
     * @var PortRegistry $inPorts
     */
    private $inPorts;

    /**
     * @var PortRegistry $outPorts
     */
    private $outPorts;

    /**
     * @param ComponentBuilderInterface $builder
     * @param string $name
     */
    public function __construct(ComponentBuilderInterface $builder, string $name)
    {
        $this->builder = $builder;
        $this->name = $name;

        $this->processes = [];
        $this->connections = [];
        $this->inPorts = new PortRegistry();
        $this->outPorts = new PortRegistry();
    }

    /**
     * @return bool|\DateInterval
     */
    public function uptime()
    {
        if( is_null($this->startupDate) )
            return false;
        return $this->startupDate->diff($this->createDateTimeWithMilliseconds());
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return is_null($this->startupDate) ? false : true;
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        // TODO: we provide running false at the moment, as when we are able to get called no node is processing data (as network is a single thread) :-)
        // TODO: this is of cause overly simplistic, as there may be data waiting on in- and/or outports using real sockets
        return false;
    }

    /**
     * @return bool|string
     */
    public function getStartupTime()
    {
        if( is_null($this->startupDate) )
            return false;
        return $this->startupDate->format('U.u');
    }

    /**
     * @param NodeSpecInterface $node
     * @return NetworkInterface
     * @throws InvalidDefinitionException
     */
    public function addNode(NodeSpecInterface $node): NetworkInterface
    {
        if (isset($this->processes[$node->getId()])) {
            throw new InvalidDefinitionException(sprintf("Node with id '%s' already exists", $node->getId()));
        }

        $process = new NetworkProcess( $node, $this->builder->build($node->getComponent()) );
        $this->processes[$node->getId()] = $process;

        return $this;
    }

    /**
     * @param string $id
     * @return NetworkInterface
     * @throws InvalidDefinitionException
     */
    public function removeNode(string $id): NetworkInterface
    {
        $this->getNode($id);
        $edges = $this->findEdgesByNode($id);
        if( count($edges) )
        {
            throw new InvalidDefinitionException(sprintf("Node '%s' still has edges", $id) );
        }

        unset($this->processes[$id]);

        return $this;
    }

    /**
     * @param string $id
     * @return NetworkProcessInterface
     * @throws NodeDoesNotExistException
     */
    public function getNode(string $id): NetworkProcessInterface
    {
        if (!isset($this->processes[$id])) {
            throw new NodeDoesNotExistException(sprintf("Node '%s' does not exist", $id) );
        }

        return $this->processes[$id];
    }

    /**
     * @return null|Graph
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * @param EdgeEndSpecInterface $source
     * @param EdgeEndSpecInterface $target
     * @param array|null $metadata
     * @return NetworkInterface
     * @throws IncompatibleDatatypeException
     * @throws InvalidDefinitionException
     * @throws NodeDoesNotExistException
     * @throws \PhpFlo\Common\Exception\PortException
     */
    public function addEdge(EdgeEndSpecInterface $source, EdgeEndSpecInterface $target, array $metadata=null): NetworkInterface
    {
        try
        {
            $from = $this->getNode($source->getNodeId());
        }
        catch ( NodeDoesNotExistException $e )
        {
            throw new NodeDoesNotExistException(
                "No process defined for source node {$source->getNodeId()}", $e->getCode(), $e
            );
        }

        try
        {
            $to = $this->getNode($target->getNodeId());
        }
        catch ( NodeDoesNotExistException $e )
        {
            throw new NodeDoesNotExistException(
                "No process defined for target node {$target->getNodeId()}", $e->getCode(), $e
            );
        }

        $socket = $this->connectPorts($from, $to, $source->getPortName(), $target->getPortName(),
            is_array($metadata) ? $metadata : []);
        $this->connections[] = $socket;

        return $this;
    }

    /**
     * @param EdgeEndSpecInterface $source
     * @param EdgeEndSpecInterface $target
     * @param array $metadata
     * @return NetworkInterface
     * @throws EdgeDoesNotExistException
     */
    public function changeEdge(EdgeEndSpecInterface $source, EdgeEndSpecInterface $target, array $metadata = []): NetworkInterface
    {
        $edges = $this->findEdges( $source, $target );
        if( count($edges) != 1 )
        {
            throw new EdgeDoesNotExistException();
        }
        $edges[0]->mergeMetadata( $metadata );

        return $this;
    }


    /**
     * @param string $nodeId
     * @return EdgeEndSpec[][]
     * @throws NodeDoesNotExistException
     */
    public function findEdgesByNode( string $nodeId )
    {
        $node = $this->getNode($nodeId);
        $edges = [];
        foreach ($this->connections as $index => $connection) {
            if ($connection->to()[self::PROCESS] == $node) {
                $edges[] = [
                    self::SOURCE => new EdgeEndSpec( $connection->from()[self::PROCESS]->getId(), $connection->from()[self::PORT] ),
                    self::TARGET => new EdgeEndSpec( $connection->to()[self::PROCESS]->getId(), $connection->to()[self::PORT] ),
                ];
            }

            if (!isset($connection->from()[self::INITIAL_DATA]) && $connection->from()[self::PROCESS] == $node) {
                $edges[] = [
                    self::SOURCE => new EdgeEndSpec( $connection->from()[self::PROCESS]->getId(), $connection->from()[self::PORT] ),
                    self::TARGET => new EdgeEndSpec( $connection->to()[self::PROCESS]->getId(), $connection->to()[self::PORT] ),
                ];
            }
        }
        return $edges;
    }

    /**
     * @param EdgeEndSpecInterface|null $source
     * @param EdgeEndSpecInterface|null $target
     * @return SocketInterface[]
     */
    protected function findEdges(EdgeEndSpecInterface $source=null, EdgeEndSpecInterface $target=null): array
    {
        /* @var SocketInterface[] $edges */
        $edges = [];
        foreach ($this->connections as $index => $connection) {
            if( (!isset($connection->from()[self::INITIAL_DATA]) &&       // if from[process] is not set, this is an initial "edge"
                    (is_null($source) ||
                        $source->getNodeId() == $connection->from()[self::PROCESS]->getId() &&
                        $source->getPortName() == $connection->from()[self::PORT])
                )
                && (is_null($target) || (
                        $target->getNodeId() == $connection->to()[self::PROCESS]->getId() &&
                        $target->getPortName() == $connection->to()[self::PORT])
                )
            )
            {
                $edges[] = $connection;
            }
        }
        return $edges;
    }

    /**
     * @param EdgeEndSpecInterface|null $source
     * @param EdgeEndSpecInterface|null $target
     * @return NetworkInterface
     */
    public function removeEdge(EdgeEndSpecInterface $source=null, EdgeEndSpecInterface $target=null): NetworkInterface
    {
        $doneSth = false;
        foreach ($this->connections as $index => $connection) {
            if( (!isset($connection->from()[self::INITIAL_DATA]) &&       // if from[process] is not set, this is an initial "edge"
                (is_null($source) ||
                    $source->getNodeId() == $connection->from()[self::PROCESS]->getId() &&
                    $source->getPortName() == $connection->from()[self::PORT])
                )
            && (is_null($target) || (
                        $target->getNodeId() == $connection->to()[self::PROCESS]->getId() &&
                        $target->getPortName() == $connection->to()[self::PORT])
                )
            )
            {
                $doneSth = true;
                echo "REMOVING CONNECTION\n";
                var_dump($connection);
                $connection->to()[self::PROCESS]->getComponent()
                    ->inPorts()
                    ->get($connection->to()[self::PORT])
                    ->detach($connection);
                unset( $this->connections[$index] );

                $connection->from()[self::PROCESS]->getComponent()
                    ->outPorts()
                    ->get($connection->from()[self::PORT])
                    ->detach($connection);
                unset( $this->connections[$index] );
                echo "Connections afterwards: ";var_dump($this->connections);
                echo "\n\n\n";
            }
        }
        if( $doneSth )
        {
            $this->connections = array_values( $this->connections );
        }

        return $this;
    }

    /**
     * @param mixed $data
     * @param EdgeEndSpecInterface $target
     * @return NetworkInterface
     * @throws InvalidDefinitionException
     * @throws NodeDoesNotExistException
     * @throws \PhpFlo\Common\Exception\PortException
     */
    public function addInitial($data, EdgeEndSpecInterface $target): NetworkInterface
    {
        $to = $this->getNode($target->getNodeId());

        $socket = $this->addHooks(new InternalSocket());
        $port = $this->connectInboundPort($socket, $to, $target->getPortName());
        $socket->connect();
        $socket->from([self::INITIAL_DATA=>$data]);
//        $socket->send($data);

        // cleanup initialization
//        $socket->disconnect();
//        $port->detach($socket);

        $this->connections[] = $socket;

        return $this;
    }

    /**
     * @param EdgeEndSpecInterface $target
     * @return mixed
     * @throws InvalidDefinitionException
     */
    public function getInitial(EdgeEndSpecInterface $target)
    {
        $node = $this->getNode($target->getNodeId());
        foreach ($this->connections as $index => $connection)
        {
            if (isset($connection->from()[self::INITIAL_DATA]) &&
                $connection->to()[self::PROCESS] == $node &&
                $connection->to()[self::PORT] == $target->getPortName())
            {
                return $connection->from()[self::INITIAL_DATA];
            }
        }
        throw new InvalidDefinitionException(sprintf("No initial to node '%s' and port '%s' found",
            $target->getNodeId(), $target->getPortName()) );
    }


    /**
     * @param EdgeEndSpecInterface $target
     * @throws InvalidDefinitionException
     */
    public function removeInitial(EdgeEndSpecInterface $target)
    {
        $node = $this->getNode($target->getNodeId());
        foreach ($this->connections as $index => $connection)
        {
            if (isset($connection->from()[self::INITIAL_DATA]) &&
                $connection->to()[self::PROCESS] == $node &&
                $connection->to()[self::PORT] == $target->getPortName())
            {
                echo "REMOVING INITIAL CONNECTION\n";
                var_dump($connection);
                $connection->to()[self::PROCESS]->getComponent()
                    ->inPorts()
                    ->get($connection->to()[self::PORT])
                    ->detach($connection);
                unset( $this->connections[$index] );
                $this->connections = array_values( $this->connections );
                return;
            }
        }
        throw new InvalidDefinitionException(sprintf("No initial to node '%s' and port '%s' found",
            $target->getNodeId(), $target->getPortName()) );
    }

    /**
     * Cleanup network state after runs.
     *
     * @return NetworkInterface
     */
    public function shutdown(): NetworkInterface
    {
        $this->startupDate = null;

        $this->componentTraitShutdown();

        foreach ($this->processes as $process) {
            $process->getComponent()->shutdown();
        }

        // explicitly destroy the $connections
        foreach ($this->connections as $connection) {
            $connection = null;
        }

        $this->graph = null;
        $this->processes = [];
        $this->connections = [];

        return $this;
    }

    /**
     * Add initialization data
     *
     * @param mixed $data
     * @param string $node
     * @param string $port
     * @return NetworkInterface
     * @throws FlowException
     */
    public function run($data, string $node, string $port): NetworkInterface
    {
        if (empty($this->graph)) {
            throw new FlowException(
                "Graph is not yet initialized!"
            );
        }

        $this->graph->addInitial($data, $node, $port);

        return $this;
    }

    /**
     * Add a flow definition as Graph object or definition file/string
     * and initialize the network processes/connections
     *
     * @param mixed $graph
     * @return NetworkInterface
     * @throws InvalidTypeException
     */
    public function boot($graph): NetworkInterface
    {
        throw new \Exception('Not implemented');

        return $this;
    }

    /**
     * @param SocketInterface $socket
     * @param NetworkProcessInterface $to
     * @param string $port
     * @return PortInterface
     * @throws InvalidDefinitionException
     * @throws \PhpFlo\Common\Exception\PortException
     */
    private function connectInboundPort(SocketInterface $socket, NetworkProcessInterface $to, string $port)
    {
        if (!$to->getComponent()->inPorts()->has($port)) {
            throw new InvalidDefinitionException("No inport {$port} defined for process {$to->getId()}");
        }

        $socket->to(
            [
                self::PROCESS => $to,
                self::PORT => $port,
            ]
        );

        return $to->getComponent()
            ->inPorts()
            ->get($port)
            ->attach($socket);
    }

    /**
     * Connect out to inport and compare data types.
     *
     * @param NetworkProcessInterface $from
     * @param NetworkProcessInterface $to
     * @param string $edgeFrom
     * @param string $edgeTo
     * @param array $metadata
     * @return SocketInterface
     * @throws IncompatibleDatatypeException
     * @throws InvalidDefinitionException
     * @throws \PhpFlo\Common\Exception\PortException
     */
    private function connectPorts(
        NetworkProcessInterface $from,
        NetworkProcessInterface $to,
        string $edgeFrom,
        string $edgeTo,
        array $metadata = []
    ): SocketInterface
    {
        if (!$from->getComponent()->outPorts()->has($edgeFrom)) {
            throw new InvalidDefinitionException("No outport {$edgeFrom} defined for process {$from->getId()}");
        }

        if (!$to->getComponent()->inPorts()->has($edgeTo)) {
            throw new InvalidDefinitionException("No inport {$edgeTo} defined for process {$to->getId()}");
        }

        $socket = $this->addHooks(
            new InternalSocket(
                [
                    self::PROCESS => $from,
                    self::PORT => $edgeFrom,
                ], [
                    self::PROCESS => $to,
                    self::PORT => $edgeTo,
                ],
                $metadata
            )
        );

        $fromType = $from->getComponent()->outPorts()->get($edgeFrom)->getAttribute('datatype');
        $toType = $to->getComponent()->inPorts()->get($edgeTo)->getAttribute('datatype');

        if (!$this->hasValidPortType($fromType)) {
            throw new InvalidDefinitionException(
                "Process {$from->getId()} has invalid outport type {$fromType}. Valid types: " .
                implode(', ', Port::$datatypes)
            );
        }

        if (!$this->hasValidPortType($toType)) {
            throw new InvalidDefinitionException(
                "Process {$to->getId()} has invalid outport type {$toType}. Valid types: " .
                implode(', ', Port::$datatypes)
            );
        }

        /* @var string $fromType */
        /* @var string $toType */

        // compare out and in ports for datatype definitions
        if (!Port::isCompatible($fromType, $toType)) {
            throw new IncompatibleDatatypeException(
                "Process {$from[self::NODE_ID]}: outport type \"{$fromType}\" of port \"{$edgeFrom}\" ".
                "does not match {$to[self::NODE_ID]} inport type \"{$toType}\" of port \"{$edgeTo}\""
            );
        }

        $from->getComponent()->outPorts()->get($edgeFrom)->attach($socket);
        $to->getComponent()->inPorts()->get($edgeTo)->attach($socket);

        return $socket;
    }

    /**
     * @return \DateTime
     */
    private function createDateTimeWithMilliseconds()
    {
        return \DateTime::createFromFormat('U.u', sprintf('%.6f', microtime(true)));
    }

    /**
     * Start network
     */
    public function startup()
    {
        $this->startupDate = $this->createDateTimeWithMilliseconds();
        foreach ($this->connections as $index => $connection)
        {
            if (isset($connection->from()[self::INITIAL_DATA]) )
            {
                $connection->send($connection->from()[self::INITIAL_DATA]);
//                  $connection->disconnect();
            }
        }
    }

    /**
     * Check datatype vs. defined types.
     *
     * @param string $type
     * @return bool
     */
    private function hasValidPortType($type): bool
    {
        return in_array($type, Port::$datatypes);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }



    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $enable
     */
    public function setDebug( bool $enable )
    {
        $this->debug = !!$enable;
    }


    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return PortRegistry
     */
    public function inPorts(): PortRegistry
    {
        return $this->inPorts;
    }

    /**
     * @return PortRegistry
     */
    public function outPorts(): PortRegistry
    {
        return $this->outPorts;
    }
}
