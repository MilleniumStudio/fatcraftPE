#!/bin/bash

SERVER='localhost'
PORT=26000
PASSWORD='6w5d4vc56cx4v'

./home/minecraft/mcrcon-0.0.5-bin-linux/mcrcon -H $SERVER -P $PORT -p $PASSWORD $1
echo $?