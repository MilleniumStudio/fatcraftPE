#!/bin/bash

echo "Installing PockerMineMP pk-1"

mkdir install
cp -r template/* install

source ../../env.sh

cp ../../plugins/AllSigns.phar install/plugins
cp ../../plugins/StatsPE.phar install/plugins

updateConfig install/plugins/LoadBalancer/config.yml
updateConfig install/plugins/StatsPE/config.yml

# copy map
#mkdir -p install/worlds/map
#cp -R ../../worlds/parkour/giantHouse/* install/worlds/map/