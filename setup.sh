#!/bin/bash

# Setup custom PocketMineMP & PocketMine-DevTools
git submodule init
git submodule update --recursive

if [ ! -d "PocketMine-MP/bin/" ]; then
    cd PocketMine-MP
    git submodule init
    git submodule update --recursive
    ./compile.sh
    cd ../
fi

# Build server core
cd cores/
./install_cores.sh
cd ../

cd plugins/
./install_plugins.sh
cd ../

cd virions/
./install_virions.sh
cd ../

cd Dockerfiles/

cd PocketMineMP-php7/
./docker_build.sh
cd ../

cd entry-lb/
./docker_build.sh
cd ../

cd lobby/
./docker_build.sh
cd ../


