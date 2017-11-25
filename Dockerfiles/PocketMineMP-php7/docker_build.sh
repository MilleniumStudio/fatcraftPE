#!/bin/bash

./install.sh

docker build --no-cache -t fatcraft/pocketmine:base .

rm -rf install/
