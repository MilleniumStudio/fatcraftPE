#!/bin/bash

echo "Installing PockerMineMP md-1"

mkdir install
cp -r template/* install

source ../../env.sh

cp ../../plugins/StatsPE.phar install/plugins
cp ../../plugins/Murder.phar install/plugins
cp ../../plugins/FatUtils.phar install/plugins
cp ../../plugins/LoadBalancer.phar install/plugins

updateConfig install/plugins/LoadBalancer/config.yml
updateConfig install/plugins/StatsPE/config.yml