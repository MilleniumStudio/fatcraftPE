#!/bin/bash

echo "Installing PockerMineMP hg-1"

mkdir install
cp -r template/* install

source ../../env.sh

updateConfig install/plugins/LoadBalancer/config.yml
#updateConfig install/plugins/PocketVote/config.yml
updateConfig install/plugins/StatsPE/config.yml
updateConfig install/plugins/SimpleAuth/config.yml

# copy map
mkdir -p install/worlds/map
cp -Rv ../../worlds/hg/HGMapSpaceship/* install/worlds/map/