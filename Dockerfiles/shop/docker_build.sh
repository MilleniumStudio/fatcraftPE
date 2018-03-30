#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:shop . --no-cache

rm -rf install/
