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
--volume $MAP_REPOSITORY:/home/minecraft/map_repository:ro \
 -d $4
}

## function to quickly start a network container
## start_multi_docker <name> <count> <image> <map>
#SERVER_PORT=19132
#INDEXES=[]
#
#start_multi_docker()
#{
#    INDEX=1
#    if [[ -n $INDEXES[$1] ]]; then
#        INDEX=${INDEXES[$1]}
#        echo "OLD index : $INDEX"
#    fi
#
#
#    for (( SERVER_ID=${INDEXES[$1]}; SERVER_ID<=$2; SERVER_ID++ ))
#    do
##        array[$1]=$((${SERVER_ID} + 1))
#
#        SERVER_PORT=$((${SERVER_PORT} + 1))
#        INDEXES[$1]=${SERVER_ID}
#
#        echo "server $1 ${SERVER_ID} $SERVER_PORT"
##        start_docker $1 $SERVER_ID $SERVER_PORT $3 $4
#    done
#    printf "Indexes $1 = %s\n" "${INDEXES[@]}"
#}
#
#start_multi_docker lobby 2 fatcraft/pocketmine:lobby lobby/lobby_bones
#start_multi_docker hg    1 fatcraft/pocketmine:hg-1  hg/HGMapSpaceship
#start_multi_docker pk    1 fatcraft/pocketmine:pk-1  parkour/giantHouse
#start_multi_docker sw    1 fatcraft/pocketmine:sw-1  sw/sw-end
#start_multi_docker sw    1 fatcraft/pocketmine:sw-2  sw/sw-alien
#start_multi_docker sw    1 fatcraft/pocketmine:sw-3  sw/sw-krum-1
#start_multi_docker bw    1 fatcraft/pocketmine:bw-1  bw/map1-4x3
#start_multi_docker bw    1 fatcraft/pocketmine:bw-2  bw/bw-krum
#start_multi_docker md    1 fatcraft/pocketmine:md-1  md/murder_krum
#start_multi_docker br    1 fatcraft/pocketmine:br-1  br/WipeOut_01_build4

# start lobbies
start_docker lobby 1 19133 fatcraft/pocketmine:lobby lobby/lobby_bones
#start_docker lobby 2 19134 fatcraft/pocketmine:lobby lobby/lobby_bones
#start_docker lobby 3 19135 fatcraft/pocketmine:lobby lobby/lobby_bones
#start_docker lobby 4 19136 fatcraft/pocketmine:lobby lobby/lobby_bones
#start_docker lobby 5 19137 fatcraft/pocketmine:lobby lobby/lobby_bones
#start_docker lobby 6 19138 fatcraft/pocketmine:lobby lobby/lobby_bones
#start_docker lobby 7 19139 fatcraft/pocketmine:lobby lobby/lobby_bones
#start_docker lobby 8 19140 fatcraft/pocketmine:lobby lobby/lobby_bones+
#start_docker lobby 9 19141 fatcraft/pocketmine:lobby lobby/lobby_bones
#start_docker lobby 10 19142 fatcraft/pocketmine:lobby lobby/lobby_bones

# start games
start_docker hg 1 19143 fatcraft/pocketmine:hg-1 hg/HGMapSpaceship
start_docker hg 2 19144 fatcraft/pocketmine:hg-2 hg/Ewok

start_docker pk 1 19145 fatcraft/pocketmine:pk-1 parkour/giantHouse

start_docker sw 1 19146 fatcraft/pocketmine:sw-1 sw/sw-end
#start_docker sw 2 19147 fatcraft/pocketmine:sw-1 sw/sw-end
start_docker sw 3 19148 fatcraft/pocketmine:sw-2 sw/sw-alien
#start_docker sw 4 19149 fatcraft/pocketmine:sw-2 sw/sw-alien
start_docker sw 5 19150 fatcraft/pocketmine:sw-3 sw/sw-krum-1
#start_docker sw 6 19151 fatcraft/pocketmine:sw-3 sw/sw-krum-1

start_docker bw 1 19152 fatcraft/pocketmine:bw-1 bw/map1-4x3
start_docker bw 2 19153 fatcraft/pocketmine:bw-1 bw/map1-4x3
start_docker bw 3 19154 fatcraft/pocketmine:bw-2 bw/bw-krum
#start_docker bw 4 19155 fatcraft/pocketmine:bw-2 bw/bw-krum

start_docker md 1 19156 fatcraft/pocketmine:md-1 md/murder_krum
#start_docker md 2 19157 fatcraft/pocketmine:md-1 md/murder_krum

start_docker br 1 19158 fatcraft/pocketmine:br-1 br/WipeOut_01_build4
#start_docker br 2 19159 fatcraft/pocketmine:br-1 br/WipeOut_01_build4

#screen -dmS FatFoward ./tools/FatForward/startFatForward

## DEBUG
#docker run --rm --name lobby-1 --hostname lobby-1 --env SERVER_NAME=lobby-1 --env SERVER_PORT=19132 --env SERVER_TYPE=lobby --env SERVER_ID=1 --env SERVER_MAP=mainLobby --publish 19132:19132 --publish 19132:19132/udp --link mysql:mysql --volume `pwd`/worlds:/home/minecraft/map_repository:ro -ti fatcraft/pocketmine:lobby
