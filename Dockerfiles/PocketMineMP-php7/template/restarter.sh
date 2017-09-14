#!/bin/bash

POCKETMINE_ARGS="";

DO_LOOP="yes"

#if [ ! -z "$DO_LOOP" ]; then
#    DO_LOOP="false"
#fi

while getopts "p:f:l" OPTION 2> /dev/null; do
	case ${OPTION} in
		p)
			PHP_BINARY="$OPTARG"
			;;
		f)
			POCKETMINE_FILE="$OPTARG"
			;;
		l)
			DO_LOOP="yes"
			;;
		\?)
			break
			;;
	esac
done
if [ "$PHP_BINARY" == "" ]; then
	if [ -f ./bin/php7/bin/php ]; then
		export PHPRC=""
		PHP_BINARY="./bin/php7/bin/php"
	elif type php 2>/dev/null; then
		PHP_BINARY=$(type -p php)
	else
		echo "Couldn't find PHP7 binary"
		exit 1
	fi
fi

if [ "$POCKETMINE_FILE" == "" ]; then
	if [ -f ./BlueLight-PHP7.phar ]; then
		POCKETMINE_FILE="./BlueLight-PHP7.phar"
	elif [ -f ./BlueLight*.phar ]; then
	    	POCKETMINE_FILE="./BlueLight*.phar"
	elif [ -f ./PocketMine-MP.phar ]; then
		POCKETMINE_FILE="./PocketMine-MP.phar"
	elif [ -f ./src/pocketmine/PocketMine.php ]; then
		POCKETMINE_FILE="./src/pocketmine/PocketMine.php"
	else
		echo "Couldn't find a valid installation"
		exit 1
	fi
fi


if [ ! -z "$SERVER_PORT" ]; then
    POCKETMINE_ARGS="$POCKETMINE_ARGS --server-port=$SERVER_PORT"
fi

if [ ! -z "$SERVER_NAME" ]; then
    POCKETMINE_ARGS="$POCKETMINE_ARGS --server-name=$SERVER_NAME"
fi

start()
{
    echo "Starting server"
    "$PHP_BINARY" $POCKETMINE_FILE $POCKETMINE_ARGS $@
    echo "Server stopped !"
}

set +e
if [ "$DO_LOOP" == "yes" ]; then
    while true; do
        start
        sleep 5
    done
else
    start
fi
