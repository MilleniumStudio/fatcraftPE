#!/bin/bash

echo "Installing PockerMineMP server base"

mkdir install
cp -r template/* install/

cp ../../cores/PocketMine-MP.phar install/

cp ../../virions/libasynql.phar install/virions
cp ../../virions/spoondetector.phar install/virions

cp ../../plugins/Hormones.phar install/plugins
cp ../../plugins/SimpleAuth.phar install/plugins

