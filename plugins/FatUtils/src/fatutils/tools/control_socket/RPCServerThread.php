<?php

namespace fatutils\tools\control_socket;

use pocketmine\Thread;
use fatutils\FatUtils;

class RPCServerThread extends Thread
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

                // extract request with the clients header.
                // In the real world of course you cannot just fix the max size to 1024..
                $request = $this->extractRequest(socket_read($client, 2048));
                var_dump($request);

                //find the correct handler
                $handlers = $_ENV['RPCHandlers'];
                var_dump($handlers);
                $uri = $request['uri'];
                if (isset($handlers[$uri]))
                {
                    $handler = new $handlers[$uri]($request);
                    $response = $handler->run();
                }
                else
                {
                    $response = $this->createResponse(404);
                }

                // write the response to the client socket
                socket_write($client, $response, strlen($response));

                // close the connetion so we can accept new ones
                socket_close($client);
            }
        }
        unset($this->socket, $this->cmd, $this->response, $this->stop);
        exit(0);
    }

    public function getThreadName(): string
    {
        return "RPCServerThread";
    }

    public static function extractRequest($header)
    {
        $request = [];
        $lines = explode("\n", $header);

        // method and uri
        list( $method, $uri ) = explode(' ', array_shift($lines));

        $headers = [];

        foreach ($lines as $line)
        {
            // clean the line
            $line = trim($line);

            if (strpos($line, ': ') !== false)
            {
                list( $key, $value ) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }
        $request['method'] = $method;
        $request['uri'] = $uri;
        $request['headers'] = $headers;
        return $request;
    }

    protected static $statusCodes = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    ];

    public function createResponse(int $p_Status = 200, $body = '')
    {
        $header = "";
        $headers = [];
        // set inital headers
        $headers[ucfirst('Date')]           = gmdate('D, d M Y H:i:s T');
        $headers[ucfirst('Content-Type')]   = 'text/html; charset=utf-8';
        $headers[ucfirst('Server')]         = 'PHPServer';

        $lines = [];
        // response status
        $lines[] = "HTTP/1.1 " . $p_Status . " " . static::$statusCodes[$p_Status];
        // add the headers
        foreach ($headers as $key => $value)
        {
            $lines[] = $key . ": " . $value;
        }
        $header = implode(" \r\n", $lines) . "\r\n\r\n";
        return $header . $body;
    }

}
