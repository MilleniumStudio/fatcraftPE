# /bin/bash

# TODO start/create mysql


# function to quickly start a network container
# start_docker <name> <port> <image>
start_docker()
{
    docker run --rm --name $1 --hostname $1 --env SERVER_NAME=$1 --env SERVER_PORT=$2 --publish $2:$2 --publish $2:$2/udp -d $3
}

# start front load-balancer
start_docker load-balancer 19132 fatcraft/pocketmine:lb

# start lobbies
start_docker lobby1 19133 fatcraft/pocketmine:lobby
start_docker lobby2 19134 fatcraft/pocketmine:lobby

# start games




## DEBUG
docker run --rm --name load-balancer --hostname load-balancer --env SERVER_NAME=entry --env SERVER_PORT=19132 --publish 19132:19132 --publish 19132:19132/udp -ti fatcraft/pocketmine:lb

docker run --rm --name lobby1 --hostname lobby1 --env SERVER_NAME=lobby1 --env SERVER_PORT=19133 --publish 19133:19133 --publish 19133:19133/udp -ti fatcraft/pocketmine:lobby
docker run --rm --name lobby2 --hostname lobby2 --env SERVER_NAME=lobby2 --env SERVER_PORT=19134 --publish 19134:19134 --publish 19134:19134/udp -ti fatcraft/pocketmine:lobby