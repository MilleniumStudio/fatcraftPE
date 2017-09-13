#!/bin/bash

echo "Installing PockerMineMP server base"

mkdir install
cp -r template/* install/

cp ../../cores/PocketMine-MP.phar install/

cp -r ../../virions/libasynql/libasynql install/virions

cp ../../plugins/LoadBalancer.phar install/plugins
cp ../../plugins/devirion.phar install/plugins