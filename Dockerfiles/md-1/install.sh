#!/bin/bash

echo "Installing PockerMineMP md-1"

mkdir install
cp -r template/* install

source ../../env.sh

cp ../../plugins/StatsPE.phar install/plugins
cp ../../plugins/Murder.phar install/plugins
cp ../../plugins/FatUtils.phar install/plugins

updateConfig install/plugins/LoadBalancer/config.yml
updateConfig install/plugins/StatsPE/config.yml

# copy map
#mkdir -p install/worlds/map
#cp -R ../../worlds/sw/sw-end/* install/worlds/map/