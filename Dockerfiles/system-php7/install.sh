#!/bin/bash

echo "Installing server base"

mkdir install
cp -r template/* install/

cp ../../PocketMine-MP/compile.sh install/
cp ../../PocketMine-MP/composer.json install/
cp ../../PocketMine-MP/composer.lock install/
