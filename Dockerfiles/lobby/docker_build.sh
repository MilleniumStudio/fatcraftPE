#!/bin/bash

./install.sh

docker build -t fatcraft/pocketmine:lobby .

rm -rf install/
