#! /bin/bash

if [ ! -f ../PocketMine-MP/bin/php7/bin/php ]; then
	cd ../PocketMine-MP/
	./compile.sh -j 8
	./bin/php7/bin/php ./bin/php7/bin/php ./bin/composer.phar install
	cd ../plugins/
fi


../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make DataDigger/ \
--out DataDigger.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make BlockPets/ \
--out BlockPets.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make MapAPI/ \
--out MapAPI.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make LoadBalancer/ \
--out LoadBalancer.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make StatsPE/ \
--out StatsPE.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make MSpawns/ \
--out MSpawns.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make MagicWE/ \
--out MagicWE.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Slapper/ \
--out Slapper.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Worlds/ \
--out Worlds.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make AllSigns/ \
--out AllSigns.phar

#php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
#--make Parkour/ \
#--out Parkour.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make FatcraftHungerGames/ \
--out FatcraftHungerGames.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make FatUtils/ \
--out FatUtils.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Lobby/ \
--out Lobby.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make BoatRacer/ \
--out BoatRacer.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make Murder/ \
--out Murder.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make FatcraftBedwars/ \
--out FatcraftBedwars.phar

../PocketMine-MP/bin/php7/bin/php -dphar.readonly=0 ../PocketMine-DevTools/src/DevTools/ConsoleScript.php \
--make FatcraftBuildBattle/ \
--out FatcraftBuildBattle.phar
