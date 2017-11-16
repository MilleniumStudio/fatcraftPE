# /bin/bash

MAP_REPOSITORY="`pwd`/worlds"
source env.sh

# function to quickly start a network container
# start_docker <name> <id> <port> <image> <map>
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

# function to quickly start a network container
# start_multi_docker <name> <count> <image> <map>
SERVER_PORT=19132

start_multi_docker()
{
    for (( i=1; i<=$2; i++ ))
    do
        SERVER_PORT=$((${SERVER_PORT} + 1))
        SERVER_ID=$i

        start_docker $1 $SERVER_ID $SERVER_PORT $3 $4
    done
}

start_multi_docker lobby 2 fatcraft/pocketmine:lobby lobby/lobby_bones
start_multi_docker hg    1 fatcraft/pocketmine:hg-1  hg/HGMapSpaceship
start_multi_docker pk    1 fatcraft/pocketmine:pk-1  parkour/giantHouse
start_multi_docker sw    1 fatcraft/pocketmine:sw-1  sw/sw-end
start_multi_docker sw    1 fatcraft/pocketmine:sw-2  sw/sw-alien
start_multi_docker sw    1 fatcraft/pocketmine:sw-3  sw/sw-krum-1
start_multi_docker bw    1 fatcraft/pocketmine:bw-1  bw/map1-4x3
start_multi_docker bw    1 fatcraft/pocketmine:bw-2  bw/bw-krum
start_multi_docker md    1 fatcraft/pocketmine:md-1  md/murder_krum
start_multi_docker br    1 fatcraft/pocketmine:br-1  br/WipeOut_01_build4

##screen -dmS FatFoward ./tools/FatForward/startFatForward

## DEBUG
#docker run --rm --name lobby-1 --hostname lobby-1 --env SERVER_NAME=lobby-1 --env SERVER_PORT=19132 --env SERVER_TYPE=lobby --env SERVER_ID=1 --env SERVER_MAP=mainLobby --publish 19132:19132 --publish 19132:19132/udp --link mysql:mysql --volume `pwd`/worlds:/home/minecraft/map_repository:ro -ti fatcraft/pocketmine:lobby
