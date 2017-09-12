#! /bin/bash

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make LoadBalancer/ \
--out LoadBalancer.phar

if [ ! -d "devirion/" ]; then
    git clone https://github.com/poggit/devirion.git
fi

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make devirion/ \
--out devirion.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make SimpleAuth/ \
--out SimpleAuth.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make StatsPE/ \
--out StatsPE.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make MSpawns/ \
--out MSpawns.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Worlds/ \
--out Worlds.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Parkour/ \
--out Parkour.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make HungerGames-UPDATED/HungerGames/ \
--out HungerGames.phar

