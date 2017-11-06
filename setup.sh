#!/bin/bash

UPDATE_POCKETMINE_CORE="no"
THREADS=1
COMPILE_BIN="no"
BUILD_POCKETMINE_CORE="no"
BUILD_POCKETMINE_DOCKER="no"


while getopts "::ujcdph" OPTION; do

    case $OPTION in
        u)
            echo "[opt] Update PocketMine-MP repository to latest version"
            UPDATE_POCKETMINE_CORE="yes"
            ;;
        j)
            echo "[opt] Set make threads to $OPTARG"
            THREADS="$OPTARG"
            ;;
        c)
            echo "[opt] Update PocketMine-MP repository to latest version"
            echo "[opt] PocketMine-MP build core set to YES"
            UPDATE_POCKETMINE_CORE="yes"
            BUILD_POCKETMINE_CORE="yes"
            ;;
        d)
            echo "[opt] Update PocketMine-MP repository to latest version"
            echo "[opt] PocketMine-MP build core set to YES"
            echo "[opt] PocketMine-MP docker image build set to YES"
            UPDATE_POCKETMINE_CORE="yes"
            BUILD_POCKETMINE_CORE="yes"
            BUILD_POCKETMINE_DOCKER="yes"
            ;;
        p)
            echo "[opt] Recompile PHP7 set to YES"
            COMPILE_BIN="yes"
            ;;
        h)
            echo "FatcraftPE setup script help :"
            echo ""
            echo "-u    Update PocketMine-MP repository to latest version"
            echo "-j 8  Set make threads to 8"
            echo "-c    Update PocketMine-MP repository & PocketMine-MP build core"
            echo "-d    Update PocketMine-MP repository & PocketMine-MP build core & PocketMine-MP docker image build"
            echo "-h    Display this help section"
            exit 0
            ;;
        \?)
            echo "Invalid option: -$OPTION$OPTARG" >&2
            exit 1
            ;;
    esac
done

# Setup custom PocketMineMP & PocketMine-DevTools
if [ "$UPDATE_POCKETMINE_CORE" == "yes" ]; then
    git submodule init
    git submodule update --recursive
fi

if [ "$BUILD_POCKETMINE_CORE" == "yes" ]; then
    rm cores/PocketMine-MP.phar
fi

if [ "$COMPILE_BIN" == "yes" ]; then
    cd PocketMine-MP
    ./compile.sh
    cd ../
fi

# Build server core
cd cores/
./install_cores.sh
cd ../

cd plugins/
./install_plugins.sh
cd ../

cd Dockerfiles/

if [ "$BUILD_POCKETMINE_DOCKER" == "yes" ]; then
    cd PocketMineMP-php7/
    ./docker_build.sh
    cd ../
fi


#cd entry-lb/
#./docker_build.sh
#cd ../

cd lobby/
./docker_build.sh
cd ../

cd hg-1/
./docker_build.sh
cd ../

cd pk-1/
./docker_build.sh
cd ../

cd sw-1/
./docker_build.sh
cd ../

cd sw-2/
./docker_build.sh
cd ../

cd sw-3/
./docker_build.sh
cd ../

cd bw-1/
./docker_build.sh
cd ..

cd bw-2/
./docker_build.sh
cd ..

cd md-1/
./docker_build.sh
cd ..
