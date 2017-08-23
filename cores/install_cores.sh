#!/bin/bash
if [ ! -d "PocketMine-DevTools/" ]; then
    git clone https://github.com/pmmp/PocketMine-DevTools.git
fi

if [ ! -d "BlueLight/" ]; then
    git clone https://github.com/BlueLightJapan/BlueLight.git
fi

if [ ! -e "PocketMine-MP.phar" ]; then
../bin/php7/bin/php -dphar.readonly=0 PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make BlueLight/ \
--relative BlueLight/ \
--entry src/pocketmine/PocketMine.php \
--out PocketMine-MP.phar
fi
