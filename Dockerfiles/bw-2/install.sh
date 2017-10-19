#!/bin/bash

echo "Installing PockerMineMP bw-2"

mkdir install
cp -r template/* install

source ../../env.sh

cp ../../plugins/StatsPE.phar install/plugins
cp ../../plugins/FatUtils.phar install/plugins
cp ../../plugins/FatcraftBedwars.phar install/plugins

updateConfig install/plugins/LoadBalancer/config.yml
updateConfig install/plugins/StatsPE/config.yml
updateConfig install/plugins/FatcraftBedwars/config.yml

# copy map
#mkdir -p install/worlds/map
#cp -R ../../worlds/mainLobby/* install/worlds/map/
