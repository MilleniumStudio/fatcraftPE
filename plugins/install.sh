#!/bin/bash

git clone https://github.com/HoverEpic/Hormones.git

../bin/php7/bin/php -dphar.readonly=0 ../cores/PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Hormones/Hormones/ \
--out Hormones.phar

git clone https://github.com/HoverEpic/SimpleAuth.git

../bin/php7/bin/php -dphar.readonly=0 ../cores/PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make SimpleAuth/ \
--out SimpleAuth.phar

git clone https://github.com/HoverEpic/EconomyS.git

../bin/php7/bin/php -dphar.readonly=0 ../cores/PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make EconomyS/EconomyAPI/ \
--out EconomyAPI.phar

git clone https://github.com/HoverEpic/StatsPE.git

../bin/php7/bin/php -dphar.readonly=0 ../cores/PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make EconomyS/EconomyAPI/ \
--out StatsPE.phar

git clone https://github.com/HoverEpic/Worlds.git

../bin/php7/bin/php -dphar.readonly=0 ../cores/PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Worlds/ \
--out Worlds.phar

git clone https://github.com/HoverEpic/Parkour.git

../bin/php7/bin/php -dphar.readonly=0 ../cores/PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Parkour/ \
--out Parkour.phar

git clone https://github.com/HoverEpic/HungerGames-UPDATED.git

../bin/php7/bin/php -dphar.readonly=0 ../cores/PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make HungerGames-UPDATED/HungerGames/ \
--out HungerGames.phar

