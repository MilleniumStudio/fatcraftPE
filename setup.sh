# /bin/bash

# setup PHP7 PocketMineMP version
#./php-installer.sh

# Build server core
cd cores/BlueLight/
./install.sh
cd ../../

cd plugins/
./install.sh
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


