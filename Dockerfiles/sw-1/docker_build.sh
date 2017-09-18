#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:sw-1 .

rm -rf install/
