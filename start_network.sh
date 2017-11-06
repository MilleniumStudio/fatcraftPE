# /bin/bash

MAP_REPOSITORY="`pwd`/worlds"
source env.sh

# function to quickly start a network container
# start_docker <name> <port> <image> <map>
start_docker()
{
    docker run \
--rm --name "$1-$2" \
--hostname "$1-$2" \
--env SERVER_NAME="$1-$2" \
--env SERVER_PORT="$3" \
--env SERVER_TYPE="$1" \
--env SERVER_ID="$2" \
--env SERVER_MAP="$5" \
--publish $3:$3 \
--publish $3:$3/udp \
--link mysql:mysql \
--volume $MAP_REPOSITORY:/home/minecraft/map_repository:ro \
 -d $4
}

# start front load-balancer
#docker run \
#--rm --name lb-1 \
#--hostname lb-1 \
#--link mysql:mysql \
#--env MYSQL_HOST=$MYSQL_HOST \
#--env MYSQL_PORT=$MYSQL_PORT \
#--env MYSQL_USER=$MYSQL_USER \
#--env MYSQL_PASS=$MYSQL_PASS \
#--env MYSQL_DATA=$MYSQL_DATA \
#--publish 19132:19132 \
#--publish 19132:19132/udp \
#-d fatcraft/pocketmine:lb

#cd tools/FatForward/binaries
#screen -dmS FatForward loadbalancer.sh
#cd ../../../

# start lobbies
start_docker lobby 1 19133 fatcraft/pocketmine:lobby mainLobby
start_docker lobby 2 19134 fatcraft/pocketmine:lobby mainLobby

# start games
start_docker hg 1 19135 fatcraft/pocketmine:hg-1 hg/HGMapSpaceship
start_docker pk 1 19136 fatcraft/pocketmine:pk-1 parkour/giantHouse
start_docker sw 1 19137 fatcraft/pocketmine:sw-1 sw/sw-end
start_docker sw 2 19138 fatcraft/pocketmine:sw-2 sw/sw-alien
start_docker bw 1 19139 fatcraft/pocketmine:bw-1 bw/map1-4x3
start_docker bw 2 19140 fatcraft/pocketmine:bw-2 bw/bw-krum
start_docker md 1 19141 fatcraft/pocketmine:md-1 md/murder_krum


## DEBUG
#docker run --rm --name lobby-1 --hostname lobby-1 --env SERVER_NAME=lobby-1 --env SERVER_PORT=19132 --env SERVER_TYPE=lobby --env SERVER_ID=1 --env SERVER_MAP=mainLobby --publish 19132:19132 --publish 19132:19132/udp --link mysql:mysql --volume `pwd`/worlds:/home/minecraft/map_repository:ro -ti fatcraft/pocketmine:lobby
