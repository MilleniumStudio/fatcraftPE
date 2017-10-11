#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:sw-2 .

rm -rf install/
