#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:bw-3 .

rm -rf install/
