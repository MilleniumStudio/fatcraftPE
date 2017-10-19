#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:bw-2 .

rm -rf install/
