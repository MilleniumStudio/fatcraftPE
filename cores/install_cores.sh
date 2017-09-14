#!/bin/bash

if [ ! -e "PocketMine-MP.phar" ]; then
../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make ../PocketMine-MP/src,../PocketMine-MP/vendor/ \
--relative ../PocketMine-MP/ \
--entry src/pocketmine/PocketMine.php \
--out PocketMine-MP.phar
fi
