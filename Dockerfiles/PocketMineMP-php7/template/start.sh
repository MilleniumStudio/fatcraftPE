#!/bin/bash

SCREEN="PocketMineMP"

PIDFILE=${APP_DIR}/${SCREEN}.pid

INVOCATION="./restarter.sh --no-wizard"

mc_start()
{
    ulimit -n 65000
    cd $APP_DIR && screen -dmS $SCREEN $INVOCATION
    screen -list | grep "\.$SCREEN" | cut -f1 -d'.' | head -n 1 | tr -d -c 0-9 > $PIDFILE
    echo "PID : $(head -1 $PIDFILE)"

    #
    # Waiting for the server to start
    #
    seconds=0

    until is_running
    do
        sleep 1
        ((seconds=$seconds+1))
        if [[ $seconds -eq 5 ]]
        then
            echo "Still not running, waiting a while longer..."
        fi
        if [[ $seconds -ge 120 ]]
        then
            echo "Failed to start, aborting."
            exit 1
        fi
    done

    echo "$SCREEN $SERVER_ID is running."
}

mc_command()
{
    echo "execute command \"$1\""
    echo "$1" >> plugins/LoadBalancer/commands.txt
    echo "\n"
}

mc_stop()
{
    if [ -z "$STOPPING" ]; then
        STOPPING=1
        #mc_command "stop"
        kill -19 $(head -1 $PIDFILE)

        #
        # Waiting for the server to shut down
        #
        seconds=0
        isInStop=1

        while is_running
        do
            sleep 1
            ((seconds=$seconds+1))
            if [[ $seconds -eq 10 ]]
            then
                echo "Still not shut down, waiting a while longer..."
            fi
            if [[ $seconds -ge 60 ]]
            then
                logger -t minecraft-init "Failed to shut down server, killing."
                echo "Failed to shut down, killing."
                force_exit
                exit 1
            fi
        done

        rm $PIDFILE
        unset isInStop
        #is_running
        echo "$SCREEN is now shut down."
    fi
}

is_running()
{
    # Checks for the minecraft servers screen session
    # returns true if it exists.

    if [ -f "$PIDFILE" ]
    then
        pid=$(head -1 $PIDFILE)
        if ps aux | grep -v grep | grep ${pid} | grep "${SCREEN}" > /dev/null
        then
            return 0
        else
            if [ -z "$isInStop" ]
            then
                if [ -z "$roguePrinted" ]
                then
                    roguePrinted=1
                    echo "Pidfile found: $pid"
                fi
            fi
            return 1
        fi
    else
        if ps aux | grep -v grep | grep "${SCREEN} ${INVOCATION}" > /dev/null
        then
            echo "No pidfile found, but server's running."
            echo "Re-creating the pidfile."

            pid=$(ps ax | grep -v grep | grep "${SCREEN} ${INVOCATION}" | cut -f1 -d' ')
            echo $pid > $PIDFILE

            return 0
        else
            return 1
        fi
    fi
}

copy_map()
{
    MAP_FOLDER="/home/minecraft/worlds/map"
    MAP_FOLDER_REPO="/home/minecraft/maps"
    mkdir -p $MAP_FOLDER

    if [ -z "$MAP" ]; then
        echo "No map set, generating.";

    else
        echo "Copying $MAP_FOLDER_REPO/$MAP/* in $MAP_FOLDER"
        cp -r $MAP_FOLDER_REPO/$MAP/* $MAP_FOLDER/
    fi
}

#listen for SIG events & target function
trap "mc_stop" SIGTERM

#start the service
#copy_map
mc_start

# wait indefinetely, display the server log
SERVER_LOG=${APP_DIR}/server.log
while is_running
do
    if [ -f "$SERVER_LOG" ] ; then
        tail -f $SERVER_LOG & wait ${!}
    fi
done

echo "Stopping container!"
exit 0
