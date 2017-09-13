#!/bin/bash

## check if user is root
#if [ "$(id -u)" != "0" ]; then
#	echo "Sorry, you are not root (use sudo $command)."
#	exit 1
#fi
#
## setup mysql user & home
#egrep -i '^mysql:' /etc/passwd
#if [ $? -eq 0 ]; then
#    userdel mysql
#fi
#
#adduser mysql --no-create-home --system --shell /bin/sh
#adduser mysql docker
#
#mkdir -p ../../data/mysql
#chown -R mysql ../../data/mysql
#
#source env.sh
#
## TODO start/create resolver & mysql
#docker run \
#--name resolver \
#--restart=always \
#--hostname resolver \
#-e EXCLUDE_LABEL=logspout.exclude \
#-v /etc/localtime:/etc/localtime:ro \
#-v /etc/timezone:/etc/timezone:ro \
#-v /var/run/docker.sock:/tmp/docker.sock \
#-v /etc/resolv.conf:/tmp/resolv.conf \
#-l logspout.exclude=true \
#-d mgood/resolvable
#
#docker run \
#--user $(id mysql -u) \
#--name mysql \
#--restart=always \
#--hostname mysql \
#-e MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASS \
#-e MYSQL_USER=$MYSQL_USER \
#-e MYSQL_PASSWORD=$MYSQL_PASS \
#-e MYSQL_DATABASE=$MYSQL_DATA \
#-v /etc/localtime:/etc/localtime:ro \
#-v /etc/timezone:/etc/timezone:ro \
#-v `pwd`/../../data/mysql:/var/lib/mysql \
#-v `pwd`/confs/mysql:/etc/mysql/conf.d \
#-p 3306:3306 \
#-d mysql:5.6.33 --max_allowed_packet=10M

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

cd hg-1/
./docker_build.sh
cd ../


