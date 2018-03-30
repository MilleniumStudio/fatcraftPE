#!/bin/bash

echo "Installing PockerMineMP shop"

mkdir install
cp -r template/* install

source ../../env.sh

cp ../../cores/PocketMine-MP.phar install/

cp ../../plugins/Lobby.phar install/plugins
cp ../../plugins/LoadBalancer.phar install/plugins
cp ../../plugins/FatUtils.phar install/plugins
cp ../../plugins/StatsPE.phar install/plugins

updateConfig install/plugins/LoadBalancer/config.yml
