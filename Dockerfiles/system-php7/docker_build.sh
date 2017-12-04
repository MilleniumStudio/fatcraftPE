#!/bin/bash

./install.sh

chmod 755 ../../PocketMine-MP/compile.sh;
docker build --no-cache -t fatcraft/system:base .

rm -rf install/
