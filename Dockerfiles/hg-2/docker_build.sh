#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:hg-2 .

rm -rf install/
