#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:br-2 .

rm -rf install/
