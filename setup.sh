# /bin/bash

# setup PHP7 PocketMineMP version
if [ ! -d "bin/" ]; then
    ./php-installer.sh
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


