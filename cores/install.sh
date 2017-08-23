#!/bin/bash

git clone https://github.com/pmmp/PocketMine-DevTools.git

git clone https://github.com/BlueLightJapan/BlueLight.git

../bin/php7/bin/php -dphar.readonly=0 PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make BlueLight/ \
--relative BlueLight/ \
--entry src/pocketmine/PocketMine.php \
--out PocketMine-MP.phar
