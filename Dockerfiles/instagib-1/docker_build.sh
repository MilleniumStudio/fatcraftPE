#!/bin/bash

./install.sh

docker build --no-cache -t fatcraft/pocketmine:instagib-1 .

rm -rf install/
