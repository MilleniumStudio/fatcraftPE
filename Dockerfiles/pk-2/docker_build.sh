#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:pk-2 . --no-cache

rm -rf install/
