#!/bin/bash

echo "Installing PockerMineMP server base"

mkdir install
cp -r template/* install/

cp ../../cores/PocketMine-MP.phar install/
cp ../../PocketMine-MP/compile.sh install/
cp ../../PocketMine-MP/composer.json install/
cp ../../PocketMine-MP/composer.lock install/