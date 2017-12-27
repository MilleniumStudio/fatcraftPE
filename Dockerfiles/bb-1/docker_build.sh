#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:bb-1 .

rm -rf install/
