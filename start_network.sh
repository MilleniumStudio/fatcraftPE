# /bin/bash

# TODO start/create mysql

# start front load-balancer
docker run --rm --name load-balancer --hostname load-balancer --env SERVER_NAME=entry --env SERVER_PORT=19132 --publish 19132:19132 --publish 19132:19132/udp -d fatcraft/pocketmine:lb

# start lobbies
docker run --rm --name lobby1 --hostname lobby1 --env SERVER_NAME=lobby1 --env SERVER_PORT=19133 --publish 19133:19132 --publish 19133:19132/udp -d fatcraft/pocketmine:lobby
docker run --rm --name lobby2 --hostname lobby2 --env SERVER_NAME=lobby2 --env SERVER_PORT=19134 --publish 19134:19132 --publish 19134:19132/udp -d fatcraft/pocketmine:lobby

# start games
