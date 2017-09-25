#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:bw-1 .

rm -rf install/
