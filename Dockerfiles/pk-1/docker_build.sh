#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:pk-1 .

rm -rf install/
