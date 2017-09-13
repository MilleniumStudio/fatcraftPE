#!/bin/bash

echo "Installing PockerMineMP lobby"

mkdir install
cp -r template/* install

source ../../env.sh

updateConfig install/plugins/LoadBalancer/config.yml
updateConfig install/plugins/PocketVote/config.yml
updateConfig install/plugins/StatsPE/config.yml
updateConfig install/plugins/SimpleAuth/config.yml

# copy map