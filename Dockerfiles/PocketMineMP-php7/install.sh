#!/bin/bash

echo "Installing PockerMineMP server base"

mkdir install
cp -r template/* install/

cp ../../cores/PocketMine-MP.phar install/

cp -r ../../virions/libasynql/libasynql install/virions
cp -r ../../virions/spoondetector/ install/virions

cp ../../plugins/Hormones.phar install/plugins
cp ../../plugins/devirion.phar install/plugins
cp ../../plugins/SimpleAuth.phar install/plugins

