#!/bin/bash

echo "Installing PockerMineMP pk-2"

mkdir install
cp -r template/* install

source ../../env.sh

cp ../../cores/PocketMine-MP.phar install/

cp ../../plugins/AllSigns.phar install/plugins
cp ../../plugins/StatsPE.phar install/plugins
cp ../../plugins/FatUtils.phar install/plugins
cp ../../plugins/LoadBalancer.phar install/plugins

updateConfig install/plugins/LoadBalancer/config.yml
updateConfig install/plugins/StatsPE/config.yml