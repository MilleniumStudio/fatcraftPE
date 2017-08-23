#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:base .

rm -rf install/
