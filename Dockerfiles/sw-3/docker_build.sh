#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:sw-3 .

rm -rf install/
