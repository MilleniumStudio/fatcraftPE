<?php

declare(strict_types = 1);

/**
 * Implementation of the Source RCON Protocol to allow remote console commands
 * Source: https://developer.valvesoftware.com/wiki/Source_RCON_Protocol
 */

namespace fatutils\tools\control_socket;

use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class RCON
{

    /** @var Server */
    private $plugin;

    /** @var resource */
    private $socket;

    /** @var int */
    private $threads;

    /** @var RCONInstance[] */
    private $workers = [];

    /** @var int */
    private $clientsPerThread;

    public function __construct(PluginBase $plugin, int $port = 8888, string $interface = "0.0.0.0", int $threads = 1, int $clientsPerThread = 50)
    {
        $this->plugin = $plugin;
        $this->plugin->getLogger()->info("Starting remote control listener");

        $this->threads = (int) max(1, $threads);
        $this->clientsPerThread = (int) max(1, $clientsPerThread);
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($this->socket === false or ! @socket_bind($this->socket, $interface, $port) or ! @socket_listen($this->socket))
        {
            throw new \RuntimeException(trim(socket_strerror(socket_last_error())));
        }

        socket_set_block($this->socket);

        for ($n = 0; $n < $this->threads; ++$n)
        {
            $this->workers[$n] = new RCONInstance($this->socket, $this->clientsPerThread);
        }

        socket_getsockname($this->socket, $addr, $port);
        $this->plugin->getLogger()->info("RCON running on $addr:$port");
    }

    public function stop()
    {
        for ($n = 0; $n < $this->threads; ++$n)
        {
            $this->workers[$n]->close();
            Server::microSleep(50000);
            $this->workers[$n]->quit();
        }
        @socket_close($this->socket);
        $this->threads = 0;
    }

    public function check()
    {
        for ($n = 0; $n < $this->threads; ++$n)
        {
            if ($this->workers[$n]->isTerminated() === true)
            {
                $this->workers[$n] = new RCONInstance($this->socket, $this->clientsPerThread);
            } elseif ($this->workers[$n]->isWaiting())
            {
                if ($this->workers[$n]->response !== "")
                {
                    $this->plugin->getLogger()->info($this->workers[$n]->response);
                    $this->workers[$n]->synchronized(function(RCONInstance $thread)
                    {
                        $thread->notify();
                    }, $this->workers[$n]);
                }
                else
                {

                    $response = new RemoteConsoleCommandSender();
                    $command = $this->workers[$n]->cmd;

                    $this->plugin->getPluginManager()->callEvent($ev = new RemoteServerCommandEvent($response, $command));

                    if (!$ev->isCancelled())
                    {
                        $this->plugin->dispatchCommand($ev->getSender(), $ev->getCommand());
                    }

                    $this->workers[$n]->response = TextFormat::clean($response->getMessage());
                    $this->workers[$n]->synchronized(function(RCONInstance $thread)
                    {
                        $thread->notify();
                    }, $this->workers[$n]);
                }
            }
        }
    }

}
