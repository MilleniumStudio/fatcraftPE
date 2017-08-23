#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:lb .

rm -rf install/
