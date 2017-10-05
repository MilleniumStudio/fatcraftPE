<?php

declare(strict_types = 1);

namespace fatutils\tools\control_socket;

use pocketmine\Thread;
use fatutils\tools\control_socket\server\Request;
use fatutils\tools\control_socket\server\Response;

class RCONInstance extends Thread
{

    public $stop;
    public $cmd;
    public $response;

    /** @var resource */
    private $socket;
    private $maxClients;
    private $waiting;

    public function isWaiting()
    {
        return $this->waiting === true;
    }

    /**
     * @param resource $socket
     * @param string   $password
     * @param int      $maxClients
     */
    public function __construct($socket, int $maxClients = 50)
    {
        $this->stop = false;
        $this->cmd = "";
        $this->response = "";
        $this->socket = $socket;
        $this->maxClients = $maxClients;
        for ($n = 0; $n < $this->maxClients; ++$n)
        {
            $this->{"client" . $n} = null;
            $this->{"status" . $n} = 0;
            $this->{"timeout" . $n} = 0;
        }

        $this->start();
    }

    public function close()
    {
        $this->stop = true;
    }

    public function run()
    {

        while ($this->stop !== true)
        {
            $this->synchronized(function()
            {
                $this->wait(2000);
            });
            $r = [$socket = $this->socket];
            $w = null;
            $e = null;
            if (socket_select($r, $w, $e, 0) === 1)
            {
                // try to get the client socket resource
                // if false we got an error close the connection and continue
                if (!$client = socket_accept($this->socket))
                {
                    socket_close($client);
                    continue;
                }

                // create new request instance with the clients header.
                // In the real world of course you cannot just fix the max size to 1024..
                $request = Request::withHeaderString(socket_read($client, 2048));
                var_dump($request);

                // execute the callback 
//                $response = call_user_func($callback, $request);

                // check if we really recived an Response object
                // if not return a 404 response object
                if (!$response || !$response instanceof Response)
                {
                    $response = Response::error(404);
                }

                // make a string out of our response
                $response = (string) $response;

                // write the response to the client socket
                socket_write($client, $response, strlen($response));

                // close the connetion so we can accept new ones
                socket_close($client);
            }

            
            
            
            
            
//            for ($n = 0; $n < $this->maxClients; ++$n)
//            {
//                $client = &$this->{"client" . $n};
//                if ($client !== null)
//                {
//                    if ($this->{"status" . $n} !== -1 and $this->stop !== true)
//                    {
//                        if ($this->{"status" . $n} === 0 and $this->{"timeout" . $n} < microtime(true)){ //Timeout
//                            $this->{"status" . $n} = -1;
//                            continue;
//                        }
//
//                        $p = $this->readPacket($client, $size, $requestID, $packetType, $payload);
//                        echo "Packet received from " . $client . " : ";
//                        var_dump($client);
//                        if ($p === false)
//                        {
//                            $this->{"status" . $n} = -1;
//                            continue;
//                        }
//                        elseif ($p === null)
//                        {
//                            continue;
//                        }
//
//                        switch ($packetType)
//                        {
//                        case 2: //Command
//                            if ($this->{"status" . $n} !== 1)
//                            {
//                                $this->{"status" . $n} = -1;
//                                continue;
//                            }
//                            if (strlen($payload) > 0)
//                            {
//                                $this->cmd = ltrim($payload);
//                                $this->synchronized(function()
//                                {
//                                    $this->waiting = true;
//                                    $this->wait();
//                                });
//                                $this->waiting = false;
//                                $this->writePacket($client, $requestID, 0, str_replace("\n", "\r\n", trim($this->response)));
//                                $this->response = "";
//                                $this->cmd = "";
//                            }
//                            break;
//                        }
//
//                    }
//                    else
//                    {
//                        @socket_set_option($client, SOL_SOCKET, SO_LINGER, ["l_onoff" => 1, "l_linger" => 1]);
//                        @socket_shutdown($client, 2);
//                        @socket_set_block($client);
//                        @socket_read($client, 1);
//                        @socket_close($client);
//                        $this->{"status" . $n} = 0;
//                        $this->{"client" . $n} = null;
//                    }
//                }
//            }
        }
        unset($this->socket, $this->cmd, $this->response, $this->stop);
        exit(0);
    }

    public function getThreadName(): string
    {
        return "ControlSocket";
    }

}
