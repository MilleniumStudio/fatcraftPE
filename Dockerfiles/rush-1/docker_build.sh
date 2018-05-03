#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:rush-1 .

rm -rf install/
