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

namespace PhpFlo\Core\Interaction;

use Evenement\EventEmitter;
use PhpFlo\Common\NetworkInterface as Net;
use PhpFlo\Common\SocketInterface;
use PhpFlo\Core\Network;

/**
 * Class InternalSocket
 *
 * @package PhpFlo\Core\Interaction
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class InternalSocket extends EventEmitter implements SocketInterface
{
    /**
     * @var bool
     */
    private $connected;

    /**
     * @var array
     */
    private $from;

    /**
     * @var array
     */
    private $to;

    /**
     * InternalSocket constructor.
     *
     * @param array $from
     * @param array $to
     */
    public function __construct(array $from = [], array $to = [])
    {
        $this->connected = false;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        if ($this->from && !$this->to) {
            return "{$this->from[Net::PROCESS][Net::NODE_ID]}.{$this->from[Net::PORT]}:ANON";
        }
        if (!$this->from) {
            return "ANON:{$this->to[Net::PROCESS][Net::NODE_ID]}.{$this->to[Net::PORT]}";
        }

        return "{$this->from[Net::PROCESS][Net::NODE_ID]}.{$this->from[Net::PORT]}:{$this->to[Net::PROCESS][Net::NODE_ID]}.{$this->to[Net::PORT]}";
    }

    /**
     * @inhertidoc
     */
    public function connect()
    {
        $this->connected = true;
        $this->emit(Net::CONNECT, [$this]);
    }

    /**
     * @param string $groupName
     */
    public function beginGroup(string $groupName)
    {
        $this->emit(Net::BEGIN_GROUP, [$groupName, $this]);
    }

    /**
     * @param string $groupName
     */
    public function endGroup(string $groupName)
    {
        $this->emit(Net::END_GROUP, [$groupName, $this]);
    }

    /**
     * @inheritdoc
     */
    public function send($data): SocketInterface
    {
        $this->emit(Net::DATA, [$data, $this]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function disconnect()
    {
        $this->connected = false;
        $this->emit(Net::DISCONNECT, [$this]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function shutdown()
    {
        $this->connected = false;
        $this->from = [];
        $this->to = [];
        $this->removeAllListeners();
        $this->emit(Net::SHUTDOWN, [$this]);
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * @param array $from
     * @return $this|array
     */
    public function from(array $from = [])
    {
        if (empty($from)) {
            return $this->from;
        } else {
            $this->from = $from;
        }

        return $this;
    }

    /**
     * @param array $to
     * @return $this|array
     */
    public function to(array $to = [])
    {
        if (empty($to)) {
            return $this->to;
        } else {
            $this->to = $to;
        }

        return $this;
    }

    public function __debugInfo()
    {
        return [
            'toProcess' => isset($this->to[Network::PROCESS]) ?
                (is_object($this->to[Network::PROCESS]) ? $this->to[Network::PROCESS]->__toString() : var_dump_string($this->to[Network::PROCESS])) : NULL,
            'toPort' => isset($this->to[Network::PORT]) ? $this->to[Network::PORT] : NULL,
            'fromProcess' => isset($this->from[Network::PROCESS]) ?
                (is_object($this->from[Network::PROCESS]) ? $this->from[Network::PROCESS]->__toString() : var_dump_string($this->from[Network::PROCESS])) : NULL,
            'fromPort' => isset($this->from[Network::PORT]) ? $this->from[Network::PORT] : NULL,
            'fromInitialData' => isset($this->from[Network::INITIAL_DATA]) ? var_export($this->from[Network::INITIAL_DATA], true) : NULL,
        ];
    }

}
