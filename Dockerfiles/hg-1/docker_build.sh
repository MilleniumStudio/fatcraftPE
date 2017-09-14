#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:hg-1 .

rm -rf install/
