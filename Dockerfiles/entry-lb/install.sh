#!/bin/bash

echo "Installing PockerMineMP load balancer"

mkdir install
cp -r template/* install

source ../../env.sh

updateConfig install/plugins/LoadBalancer/config.yml
updateConfig install/plugins/PocketVote/config.yml

#TODO COPY MAP
