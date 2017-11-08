#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:br-1 .

rm -rf install/
