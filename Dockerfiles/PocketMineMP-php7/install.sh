#!/bin/bash

echo "Installing PockerMineMP server base"

mkdir install
cp -r template/* install/

cp ../../cores/PocketMine-MP.phar install/
cp ../../plugins/FatUtils.phar install/plugins
