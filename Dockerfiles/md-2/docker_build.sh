#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:md-2 .

rm -rf install/
