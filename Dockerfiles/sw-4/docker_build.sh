#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:sw-4 .

rm -rf install/
