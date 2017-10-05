<?php

namespace fatutils\tools\control_socket;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;

class RPCServer
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

    public $handlers = array();

    public function __construct(PluginBase $plugin, int $port = 8888, string $interface = "0.0.0.0", int $threads = 1, int $clientsPerThread = 50)
    {
        $this->plugin = $plugin;
        $this->plugin->getLogger()->info("[RPCServer] Starting listener");

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
            $this->workers[$n] = new RPCServerThread(& $this->handlers, $this->socket, $this->clientsPerThread);
        }

        socket_getsockname($this->socket, $addr, $port);
        $this->plugin->getLogger()->info("[RPCServer]  running on $addr:$port");
        $this->registerHandler("\helloworld", handlers\HelloWorldHandler::class);
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

    public function registerHandler(string $path, $class)
    {
        $this->handlers[$path] = $class;
        \fatutils\FatUtils::getInstance()->getLogger()->info("[RPCServer] Registering handler " . $path . "");
        var_dump($this->handlers);
    }
}
