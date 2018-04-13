#!/bin/bash

echo "Installing PockerMineMP shop"

mkdir install
cp -r template/* install

source ../../env.sh

cp ../../cores/PocketMine-MP.phar install/

cp ../../plugins/FatUtils.phar install/plugins
cp ../../plugins/LoadBalancer.phar install/plugins
cp ../../plugins/BuycraftPM.phar install/plugins

updateConfig install/plugins/LoadBalancer/config.yml
