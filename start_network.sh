# /bin/bash

MAP_REPOSITORY="`pwd`/worlds"

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
#start_docker lb 1 19132 fatcraft/pocketmine:lb

# start lobbies
start_docker lobby 1 19132 fatcraft/pocketmine:lobby mainLobby
#start_docker lobby 2 19133 fatcraft/pocketmine:lobby mainLobby

# start games
start_docker hg 1 19134 fatcraft/pocketmine:hg-1 hg/HGMapSpaceship
start_docker pk 1 19135 fatcraft/pocketmine:pk-1 parkour/giantHouse
start_docker sw 1 19136 fatcraft/pocketmine:sw-1 sw/sw-end
start_docker sw 2 19137 fatcraft/pocketmine:sw-2 sw/sw-alien
start_docker bw 1 19138 fatcraft/pocketmine:bw-1 bw/map1-4x3


## DEBUG
#docker run --rm --name lobby-1 --hostname lobby-1 --env SERVER_NAME=lobby-1 --env SERVER_PORT=19132 --env SERVER_TYPE=lobby --env SERVER_ID=1 --env SERVER_MAP=mainLobby --publish 19132:19132 --publish 19132:19132/udp --link mysql:mysql --volume `pwd`/worlds:/home/minecraft/map_repository:ro -ti fatcraft/pocketmine:lobby
