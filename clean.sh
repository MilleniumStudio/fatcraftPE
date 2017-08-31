# /bin/bash

# clean PHP7 PocketMineMP version
rm -rf bin/

# clean server core
rm -rf cores/BlueLight
rm -rf cores/PocketMine-DevTools
rm cores/*.phar

# clean plugins
rm plugins/*.phar
rm -rf plugins/EconomyS
rm -rf plugins/Hormones
rm -rf plugins/devirion
rm -rf plugins/HungerGames-UPDATED
rm -rf plugins/Parkour
rm -rf plugins/SimpleAuth
rm -rf plugins/StatsPE

# clean virions
rm virions/*.phar
rm virions/*.php
rm -rf virions/libasynql
rm -rf virions/spoondetector
