#!/bin/bash

./install.sh

docker build --no-cache -t fatcraft/pocketmine:battleRoyal-1 .

rm -rf install/
