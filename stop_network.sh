# /bin/bash

killd ()
{
    for session in $(screen -ls | grep -o '[0-9]\+')
        do screen -S "${session}" -X quit;
    done
}

killd

docker stop -t 0 lobby-1
docker stop -t 0 lobby-2
docker stop -t 0 lobby-3
docker stop -t 0 lobby-4
docker stop -t 0 lobby-5
docker stop -t 0 lobby-6
docker stop -t 0 lobby-7
docker stop -t 0 lobby-8
docker stop -t 0 lobby-9
docker stop -t 0 lobby-10

docker stop -t 0 hg-1
docker stop -t 0 hg-2
docker stop -t 0 pk-1
docker stop -t 0 sw-1
docker stop -t 0 sw-2
docker stop -t 0 sw-3
docker stop -t 0 sw-4
docker stop -t 0 sw-5
docker stop -t 0 sw-6
docker stop -t 0 bw-1
docker stop -t 0 bw-2
docker stop -t 0 bw-3
docker stop -t 0 bw-4
docker stop -t 0 md-1
docker stop -t 0 md-2
docker stop -t 0 br-1
docker stop -t 0 br-2
