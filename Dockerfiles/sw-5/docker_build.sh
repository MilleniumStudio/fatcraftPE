#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:sw-5 .

rm -rf install/
