#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:md-3 .

rm -rf install/
