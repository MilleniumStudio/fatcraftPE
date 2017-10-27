#!/bin/bash

echo "Installing PockerMineMP lobby"

mkdir install
cp -r template/* install

source ../../env.sh

cp ../../plugins/Slapper.phar install/plugins
cp ../../plugins/AllSigns.phar install/plugins
cp ../../plugins/StatsPE.phar install/plugins
cp ../../plugins/Lobby.phar install/plugins
cp ../../plugins/FatUtils.phar install/plugins
cp ../../plugins/LoadBalancer.phar install/plugins

updateConfig install/plugins/LoadBalancer/config.yml
updateConfig install/plugins/PocketVote/config.yml
updateConfig install/plugins/StatsPE/config.yml