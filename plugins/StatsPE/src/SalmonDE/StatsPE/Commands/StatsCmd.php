<?php
namespace SalmonDE\StatsPE\Commands;

use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use SalmonDE\StatsPE\CustomEntries;
use SalmonDE\StatsPE\FloatingTexts\CustomFloatingText\FloatingTop;

class StatsCmd extends \pocketmine\command\PluginCommand implements \pocketmine\command\CommandExecutor
{

    public function __construct(\SalmonDE\StatsPE\Base $owner)
    {
        parent::__construct('stats', $owner);
//        $this->setPermission('statspe.cmd.stats');
        $this->setDescription($owner->getMessage('commands.stats.description'));
        $this->setUsage($owner->getMessage('commands.stats.usage'));
        $this->setExecutor($this);
    }

    public function onCommand(\pocketmine\command\CommandSender $sender, \pocketmine\command\Command $cmd, string $label, array $args): bool
    {
        if (!isset($args[0])) {
            if (!$sender instanceof Player) {
                return false;
            }
            $args[0] = $sender->getName();
        }

        // Custom Commands
        if ($sender->isOp() && isset($args[0])) {
            if ($args[0] == "test") {
                if ($sender instanceof Player && isset($args[1])) {
                    $entryName = $args[1];
                    if (isset($args[2]) && $args[2] == "add" && isset($args[3])) {
                        CustomEntries::getInstance()->modIntEntry($entryName, $sender, (int)$args[3]);
                    } else if (isset($args[2])) {
                        CustomEntries::getInstance()->setEntry($entryName, $sender, $args[2]);
                    }
                    $sender->sendMessage($entryName . ": " . CustomEntries::getInstance()->getEntry($entryName, $sender));
                    return true;
                }
                return false;
            } else if ($args[0] == "top") {
                $statName = "XP";
                if (isset($args[1]))
                    $statName = $args[1];
                $title = $statName;
                if (isset($args[2]))
                    $title = $args[2];
                $nbLines = 5;
                if (isset($args[3]))
                    $nbLines = (int)$args[3];
                (new FloatingTop($statName, $sender->x, $sender->y, $sender->z, $sender->getLevel(), $nbLines, $title))->saveInYML();
                return true;
            } else if ($args[0] == "?" || $args[0] == "help") {
                $sender->sendMessage(
                    "/stats test <statName> [add] <value> : set or add a value to the specified stat\n".
                    "/stats top [statName [title [nbLine]]] : add a leaderboard to the current location. Default value are statName = 'XP', title = statName, nbLines = 5\n"
                );
                return true;
            }
        }
        else if (is_array($data = $this->getPlugin()->getDataProvider()->getAllData($args[0]))) {
            $text = str_replace('{value}', $data['Username'], $this->getPlugin()->getMessage('general.header'));
            foreach ($this->getPlugin()->getDataProvider()->getEntries() as $entry) {
                if ($sender->hasPermission('statspe.entry.' . $entry->getName())) {
                    switch ($entry->getName()) {
                        case 'FirstJoin':
                            $p = $sender->getServer()->getOfflinePlayer($args[0]);
                            $value = date($this->getPlugin()->getConfig()->get('dateFormat'), $p->getFirstPlayed() / 1000);
                            break;

                        case 'LastJoin':
                            $p = $sender->getServer()->getOfflinePlayer($args[0]);
                            $value = date($this->getPlugin()->getConfig()->get('dateFormat'), $p->getLastPlayed() / 1000);
                            break;

                        case 'OnlineTime':
                            $seconds = $data['OnlineTime'];
                            if (($p = $sender->getServer()->getPlayerExact($data['Username'])) instanceof Player) {
                                $seconds += round(time() - ($p->getLastPlayed() / 1000));
                            }

                            $value = \SalmonDE\StatsPE\Utils::getPeriodFromSeconds($seconds);
                            break;

                        case 'K/D':
                            $value = \SalmonDE\StatsPE\Utils::getKD($data['KillCount'], $data['DeathCount']);
                            break;

                        case 'Online':
                            $value = $data['Online'] ? $this->getPlugin()->getMessage('commands.stats.true') : $this->getPlugin()->getMessage('commands.stats.false');
                            break;

                        default:
                            $value = $data[$entry->getName()];
                    }
                    $text .= TF::RESET . "\n" . TF::AQUA . $entry->getName() . ': ' . TF::GOLD . $value;
                }
            }
            $sender->sendMessage($text);
        } else {
            $sender->sendMessage(TF::RED . str_replace('{player}', $args[0], $this->getPlugin()->getMessage('commands.stats.notFound')));
        }
        return true;
    }
}
