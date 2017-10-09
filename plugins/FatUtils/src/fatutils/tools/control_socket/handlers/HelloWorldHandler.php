<?php

namespace fatutils\tools\control_socket\handlers;

class HelloWorldHandler extends RPCHandler
{
    public function run()
    {
        return "Hello World !";
    }
}

