<?php

namespace fatutils\tools\control_socket\handlers;

abstract class RPCHandler
{
    private $request;

    public function __construct($request = [])
    {
        $this->request = $request;
    }

    abstract public function run();
}

