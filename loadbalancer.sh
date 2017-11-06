#! /bin/bash

echo "Starting LoadBalancer FatForward"

source env.sh

export MYSQL_HOST=$MYSQL_HOST
export MYSQL_PORT=$MYSQL_PORT
export MYSQL_USER=$MYSQL_USER
export MYSQL_PASS=$MYSQL_PASS
export MYSQL_DATA=$MYSQL_DATA

cd tools/FatForward/binaries
mono FatForward.exe
