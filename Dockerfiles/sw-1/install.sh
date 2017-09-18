#!/bin/bash

echo "Installing PockerMineMP sw-1"

mkdir install
cp -r template/* install

source ../../env.sh

cp ../../plugins/SimpleAuth.phar install/plugins
cp ../../plugins/StatsPE.phar install/plugins
cp ../../plugins/FatcraftHungerGames.phar install/plugins
cp ../../plugins/FatUtils.phar install/plugins

updateConfig install/plugins/LoadBalancer/config.yml
updateConfig install/plugins/StatsPE/config.yml
updateConfig install/plugins/SimpleAuth/config.yml

# copy map
mkdir -p install/worlds/map
cp -R ../../worlds/sw/sw-end/* install/worlds/map/