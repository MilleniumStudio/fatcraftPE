#!/usr/bin/env bash


# for mysql docker container
MYSQL_ROOT_PASS="sd2354fcv453df4v35df4v536df454dfv654dfvj"

# for building docker images
MYSQL_HOST="mysql"
MYSQL_PORT=3306
MYSQL_USER="fatcraftpe"
MYSQL_PASS="s54c5xcw4v56xc74g534cxb54g65b4gf654145bg"
MYSQL_DATA="fatcraft_pe"

# external IP (auto) OR local IP
SERVER_IP="192.168.4.10"

updateConfig() {
    sed -i 's/<<MYSQL_USER>>/'$MYSQL_USER'/g' $1
    sed -i 's/<<MYSQL_PASS>>/'$MYSQL_PASS'/g' $1
    sed -i 's/<<MYSQL_DATA>>/'$MYSQL_DATA'/g' $1
    sed -i 's/<<MYSQL_HOST>>/'$MYSQL_HOST'/g' $1
    sed -i 's/<<MYSQL_PORT>>/'$MYSQL_PORT'/g' $1

    sed -i 's/<<SERVER_IP>>/'$SERVER_IP'/g' $1

    echo "file $1 builded!"
}
