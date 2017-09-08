#!/usr/bin/env bash

##----------------------------
## MAIN OPTIONS
##----------------------------
FATCRAFT_PE_FOLDER="./"
POKETMINE_FOLDER="./PocketMine-MP"

PHP_EXEC=${POKETMINE_FOLDER}/bin/php/php
START_SCRIPT=start.sh

##----------------------------
## TEMPLATES DECLARATION
##----------------------------
TEMPLATE_NAME=$1
if [[ $1 == "hg1" ]]; then
    TEMPLATE_PATH='./hg1'
    TEMPLATE_MAP_NAME='hg/hg1'
    declare -a TEMPLATE_PLUGINS=("FatUtils" "AllSigns" "FatcraftHungerGames")
else
    echo "USAGE: start.sh <templateName> [soft]"
    echo "Current templates: hg1|..."
    exit 1
fi

##---------------
## FUNCTIONS
##---------------
cpCore()
{
    if [ ! -d "$1" ]; then
        return 1
    fi

    cp -r ${POKETMINE_FOLDER}/bin/ \
          ${POKETMINE_FOLDER}/src/ \
          ${POKETMINE_FOLDER}/server.properties \
          ${POKETMINE_FOLDER}/ops.txt \
          ${POKETMINE_FOLDER}/start.cmd \
          $1
}

compilePlugin()
{
    if [ ! -d "$1" ]; then
        echo "Incorrect Plugin name ${1}"
        return 1
    fi

    rm -rf ${1}.phar
    ${PHP_EXEC} -dphar.readonly=0 ${FATCRAFT_PE_FOLDER}/cores/PocketMine-DevTools/src/DevTools/ConsoleScript.php \
        --make ${1}/ \
        --out ${1}.phar
}

##---------------
## MAIN SCRIPT
##---------------
echo "====< Preparing ${TEMPLATE_NAME} >==== (in ${TEMPLATE_PATH})"

if [[ ! $2 == "soft" ]]; then
    echo "Removing old data..."
    rm -rf ${TEMPLATE_PATH}
    mkdir ${TEMPLATE_PATH}

    echo "Copying Core..."
    cpCore ${TEMPLATE_PATH}
fi

echo "Copying map..."
rm -rf ${TEMPLATE_PATH}/worlds
mkdir ${TEMPLATE_PATH}/worlds
cp -rf ${FATCRAFT_PE_FOLDER}/worlds/${TEMPLATE_MAP_NAME} ${TEMPLATE_PATH}/worlds/map

echo "Compiling & Copying plugins..."
rm -rf ${TEMPLATE_PATH}/plugins/
mkdir ${TEMPLATE_PATH}/plugins/
for i in "${TEMPLATE_PLUGINS[@]}"
do
   echo "- $i"
   PLUGIN_PATH=${FATCRAFT_PE_FOLDER}/plugins/$i
   compilePlugin ${PLUGIN_PATH} >> /dev/null
   cp -r ${PLUGIN_PATH}.phar ${TEMPLATE_PATH}/plugins/

   if [ -d "${PLUGIN_PATH}/resources" ]; then
        mkdir ${TEMPLATE_PATH}/plugins/$i
        cp -r ${PLUGIN_PATH}/resources/* ${TEMPLATE_PATH}/plugins/$i/
   fi

   if [ -d "${PLUGIN_PATH}/templates/${TEMPLATE_NAME}" ]; then
        mkdir ${TEMPLATE_PATH}/plugins/$i
        cp -r ${PLUGIN_PATH}/templates/${TEMPLATE_NAME}/* ${TEMPLATE_PATH}/plugins/$i/
   fi
done

echo "====< Launching ${TEMPLATE_NAME} >===="
${TEMPLATE_PATH}/${START_SCRIPT}
