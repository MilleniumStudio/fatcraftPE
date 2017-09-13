#!/bin/bash

echo "Installing PockerMineMP server base"

mkdir install
cp -r template/* install/

cp ../../cores/PocketMine-MP.phar install/

cp ../../plugins/LoadBalancer.phar install/plugins