#! /bin/bash

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make LoadBalancer/ \
--out LoadBalancer.phar

if [ ! -d "devirion/" ]; then
    git clone https://github.com/poggit/devirion.git
fi

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make devirion/ \
--out devirion.phar

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make SimpleAuth/ \
--out SimpleAuth.phar

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make StatsPE/ \
--out StatsPE.phar

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make MSpawns/ \
--out MSpawns.phar

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Slapper/ \
--out Slapper.phar

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Worlds/ \
--out Worlds.phar

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make AllSigns/ \
--out AllSigns.phar

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Parkour/ \
--out Parkour.phar

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make FatcraftHungerGames/ \
--out FatcraftHungerGames.phar

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make FatUtils/ \
--out FatUtils.phar

php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Lobby/ \
--out Lobby.phar
