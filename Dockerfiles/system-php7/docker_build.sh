#!/bin/bash

./install.sh

docker build --no-cache -t fatcraft/system:base .

rm -rf install/
