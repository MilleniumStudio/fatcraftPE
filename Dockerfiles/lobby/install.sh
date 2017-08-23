#!/bin/bash

echo "Installing PockerMineMP lobby"

mkdir install
cp -r template/* install

source ../../env.sh

updateConfig install/plugins/Hormones/config.yml
updateConfig install/plugins/SimpleAuth/config.yml
