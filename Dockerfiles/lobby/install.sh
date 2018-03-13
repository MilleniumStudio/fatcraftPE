#!/bin/bash

echo "Installing PockerMineMP lobby"

mkdir install
cp -r template/* install

source ../../env.sh

cp ../../cores/PocketMine-MP.phar install/

cp ../../plugins/StatsPE.phar install/plugins
cp ../../plugins/Lobby.phar install/plugins
cp ../../plugins/FatUtils.phar install/plugins
cp ../../plugins/LoadBalancer.phar install/plugins

updateConfig install/plugins/LoadBalancer/config.yml
updateConfig install/plugins/StatsPE/config.yml
