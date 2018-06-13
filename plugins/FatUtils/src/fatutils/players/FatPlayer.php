<?php
/**
 * Created by PhpStorm.
 * User: naphtaline
 * Date: 06/09/17
 * Time: 10:48
 */

namespace fatutils\players;

use fatutils\permission\PermissionManager;
use fatutils\scores\ScoresManager;
use fatutils\shop\ShopItem;
use fatutils\shop\ShopManager;
use fatutils\spawns\Spawn;
use fatutils\teams\Team;
use fatutils\teams\TeamsManager;
use fatutils\tools\schedulers\DelayedExec;
use fatutils\tools\TextFormatter;
use fatutils\ui\impl\LanguageWindow;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use fatcraft\loadbalancer\LoadBalancer;
use fatutils\FatUtils;
use fatutils\tools\schedulers\Timer;
use fatutils\events\LanguageUpdatedEvent;
use libasynql\result\MysqlResult;
use libasynql\DirectQueryMysqlTask;
use SalmonDE\StatsPE\CustomEntries;

class Kit
{
	const SLOT_KIT_HEAD = "kit_head";
	const SLOT_KIT_CHEST = "kit_chest";
	const SLOT_KIT_PANTS = "kit_pants";
	const SLOT_KIT_BOOTS = "kit_boots";
	const SLOT_KIT_HELD = "kit_held";
}

class FatPlayer
{
    const PLAYER_STATE_WAITING = 0;
    const PLAYER_STATE_PLAYING = 1;

    public static $m_OptionDisplayHealth = true;
    public static $m_OptionDisplayGroupPrefix = true;
    public static $m_OptionDisplayTeamPrefix = true;
	public static $m_OptionDisplayNameTag = true;
	public static $m_OptionDisplayLevel = false;

	private $m_Player;
    private $m_Name;
    private $m_Level;
    private $m_Xp;
    private $m_State = 0;
    private $m_OutOfGame = false;

    private $m_Data = [];

    private $m_Spawn = null;
    private $m_Language = TextFormatter::LANG_ID_DEFAULT;
    private $m_Email = null;
    private $m_permissionGroup = "default";
    private $m_FSAccount = null;

    private $m_MutedTimestamp = 0;

    private $m_Fatsilver = 0;
	private $m_Fatgold = 0;

    private $m_slots = [];
	private $m_BoughtShopItems = [];
	private $m_KitItems = [];

	private $m_isPreviewing = false;

	private $m_dataRelativeToContext; // use this to whatever you need depending on the gamemode for example

    private $m_currentHotbarSlot; // atm only used for paintball mechanics

    private $m_isTop1BattleRoyale = false;

    public $m_justOppendMenu = false;

    /**
     * FatPlayer constructor.
     * @param Player $p_Player
     */
    public function __construct(Player $p_Player)
    {
        $this->setPlayer($p_Player);
    }

    public function setPlayer(Player $p_Player)
    {
        $this->m_Player = $p_Player;
        $this->m_Name = $p_Player->getName();
        $this->initData();
    }

    public function setPlaying()
    {
        $this->m_State = FatPlayer::PLAYER_STATE_PLAYING;
    }

    public function isWaiting()
    {
        return $this->m_State === FatPlayer::PLAYER_STATE_WAITING;
    }

    public function isPlaying()
    {
        return $this->m_State === FatPlayer::PLAYER_STATE_WAITING;
    }

    public function isOutOfGame()
    {
        return $this->m_OutOfGame;
    }

    public function setOutOfGame(bool $p_OutOfGame = true)
    {
        $this->m_OutOfGame = $p_OutOfGame;
    }

    public function addData(string $p_Key, $value)
    {
        $l_OldData = $this->getData($p_Key, 0);
        if (is_numeric($l_OldData))
            $this->m_Data[$p_Key] = $l_OldData + $value;
    }

    public function setData(string $p_Key, $value)
    {
        $this->m_Data[$p_Key] = $value;
    }

    public function getData(string $p_Key, $p_DefaultValue)
    {
        if (array_key_exists($p_Key, $this->m_Data))
            return $this->m_Data[$p_Key];
        else
            return $p_DefaultValue;
    }

    public function getDatas(): array
    {
        return $this->m_Data;
    }

    public function getTeam(): ?Team
    {
        return TeamsManager::getInstance()->getPlayerTeam($this->getPlayer());
    }

    public function getSpawn(): ?Spawn
    {
        return $this->m_Spawn;
    }

    public function getSpawnPosition(): ?Position
    {
        return (!is_null($this->getSpawn()) ? $this->getSpawn()->getLocation() : $this->getPlayer()->getLevel()->getSpawnLocation());
    }

    public function setSpawn(Spawn $p_Spawn)
    {
        $this->m_Spawn = $p_Spawn;
    }

    public function addScore(string $p_Key, int $p_Value)
    {
        if (!isset($this->m_Scores[$p_Key]))
            $this->m_Scores[$p_Key] = $p_Value;
        else {
            $l_OldValue = $this->m_Scores[$p_Key];
            if (is_numeric($l_OldValue))
                $this->m_Scores[$p_Key] = $l_OldValue + $p_Value;
        }
    }

    public function getName(): string
    {
        return $this->m_Name;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->m_Player;
    }

    public function updatePlayerNames()
    {
        $l_Ret = "";

        if (self::$m_OptionDisplayNameTag)
		{
			// TEAM PREFIX
			$l_Team = TeamsManager::getInstance()->getPlayerTeam($this->getPlayer());
			if (self::$m_OptionDisplayTeamPrefix && isset($l_Team))
				$l_Ret .= $l_Team->getPrefix() . TextFormat::WHITE . TextFormat::RESET;

			// GROUP PREFIX
			if (self::$m_OptionDisplayGroupPrefix)
			{
				$l_GroupPrefix = PermissionManager::getInstance()->getFatPlayerGroupPrefix($this);
				if (strlen($l_GroupPrefix) > 0)
					$l_Ret .= TextFormat::RESET . $l_GroupPrefix . TextFormat::RESET;
			}

			$l_Ret .= $this->getPlayer()->getName() . TextFormat::RESET . TextFormat::WHITE;

			$this->getPlayer()->setDisplayName($l_Ret);

			// HEALTH BAR
			if (self::$m_OptionDisplayHealth)
			{
				$l_HealthBar = "\n[" . TextFormat::RED;
				$l_PlayerHealth = $this->getPlayer()->getHealth() * 10 / $this->getPlayer()->getMaxHealth();
				for ($i = 0; $i < 10; $i++)
				{
					if ($l_PlayerHealth > 0)
					{
						$l_HealthBar .= "█";
						$l_PlayerHealth--;
					} else
						$l_HealthBar .= " ";
				}
				$l_HealthBar .= TextFormat::RESET . "]";

				$l_Ret .= $l_HealthBar;
			}

			if (self::$m_OptionDisplayLevel)
            {
                $l_level = "";
                if ($this->m_Level < 20)
                    $l_level = "\n" . TextFormat::YELLOW . "Level "  . TextFormat::GREEN . $this->m_Level;
                else
                    $l_level = "\n" . TextFormat::YELLOW . $this->m_Level;
                $l_level .= TextFormat::RESET;

                if ($this->m_isTop1BattleRoyale)
                    $l_level .= " - " . TextFormat::GOLD . TextFormat::BOLD . "Top 1";

                $l_level .= TextFormat::RESET;
                $l_Ret .= $l_level;
            }

		}

        $this->getPlayer()->setNameTag($l_Ret);
    }

    private function initData()
    {
        $l_StartMillisec = microtime(true);
        $l_Exist = false;
        $result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
            "SELECT * FROM players WHERE uuid = ?", [
                ["s", $this->getPlayer()->getUniqueId()]
            ]);
        $l_EndMillisec = microtime(true);
        if (($result instanceof \libasynql\result\MysqlSelectResult) and count($result->rows) == 1) {
            if (count($result->rows) == 1) {
                $this->m_Email = $result->rows[0]["email"];
                $this->m_Language = $result->rows[0]["lang"];
                $this->m_Level = $result->rows[0]["level"];
                $this->m_permissionGroup = $result->rows[0]["permission_group"];
                if ($this->m_permissionGroup == null || $this->m_permissionGroup == "")
                    $this->m_permissionGroup = "default";
                $this->m_MutedTimestamp = $result->rows[0]["muted"];

				$this->m_Fatsilver = $result->rows[0]["fatsilver"];
				$this->m_Fatgold = $result->rows[0]["fatgold"];

				$l_RawBoughtShopItem = $result->rows[0]["shop_possessed"];
				if (is_string($l_RawBoughtShopItem) && strlen($l_RawBoughtShopItem) > 2)
					$this->m_BoughtShopItems = json_decode($result->rows[0]["shop_possessed"]);

				if (ShopManager::$m_OptionAutoEquipSavedItems)
				{
					$l_RawEquippedItem = $result->rows[0]["shop_equipped"];
					new DelayedExec( function() use ($l_RawEquippedItem)
					{
						$this->reequipRawShopItems($l_RawEquippedItem);
					}, 5);
				}
				if ($result->rows[0]["kit_items"] != null)
					$this->m_KitItems = json_decode($result->rows[0]["kit_items"], true);
				$l_Exist = true;
                FatUtils::getInstance()->getLogger()->info("[FatPlayer] " . $this->getPlayer()->getName() . " exist in database, loading took " . (($l_EndMillisec - $l_StartMillisec) * 1000) . "ms");
            }
        }
        if (!$l_Exist) {
            FatUtils::getInstance()->getLogger()->info("[FatPlayer] " . $this->getPlayer()->getName() . " not exist in database, creating...");
            FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
                new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                    "INSERT INTO players (name, uuid, xuid) VALUES (?, ?, ?)", [
                        ["s", $this->getPlayer()->getName()],
                        ["s", $this->getPlayer()->getUniqueId()],
                        ["s", $this->getPlayer()->getXuid()]
                    ]
                ));

            // process first login
            new DelayedExec(function ()
			{
				new LanguageWindow($this->getPlayer());
			}, 40);

            $result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
                "SELECT scores.`player`, COUNT(scores.`position`) AS number FROM scores  WHERE serverType = \"battleRoyale\" && POSITION = 100 && MONTH(scores.`date`) >= MONTH(NOW()) GROUP BY player ORDER BY number DESC LIMIT 1", []
            );
            if (($result instanceof MysqlSelectResult) and isset($result->rows[0]))
            {
                if ($result->rows[0]["player"] == $this->getPlayer()->getUniqueId())
                    $this->m_isTop1BattleRoyale = true;
            }
        }


        //////// handle Gifts to players ////////

        $giftResult = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
            "SELECT * FROM player_gift WHERE name = ?" , [
                ["s", $this->getPlayer()->getName()]
            ]);
        if ($giftResult instanceof MysqlErrorResult)
        {
            echo ($giftResult->getException());
            return;
        }
        if ($giftResult instanceof \libasynql\result\MysqlSelectResult) {
        foreach ($giftResult->rows as $row)
            {
                $lGift = ShopManager::getInstance()->getShopItemByKey($this->getPlayer(), $row["gift_type"]);
                if ($lGift != null)
                {
                    $this->addBoughtShopItem($lGift, -1, -1);

                    MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
                        "DELETE FROM player_gift WHERE name = ? AND gift_type = ?", [
                            ["s", $this->getPlayer()->getName()],
                            ["s", $row["gift_type"]]
                        ]
                    );
                    echo ($row["gift_type"] . " given to " . $this->getPlayer()->getName() . "\n");
                }
            }
        }

        PermissionManager::getInstance()->updatePermissions($this);
    }

    private function parseKitItemFromDb(string $p_strItem) : string
	{
		$itemName = $p_strItem;
		$itemName = explode('(', $itemName)[0];
		$itemName = substr($itemName, 5, -1);
		$itemName = str_replace(" ", "_", $itemName);
		$itemName = strtoupper($itemName);

		return $itemName;
	}

    public function equipKitToPlayer()
	{
		foreach ($this->m_KitItems as $item => $value)
		{
			$itemName = $this->parseKitItemFromDb($value);
            echo("item :");
            var_dump($itemName);
			$tempItem = ItemFactory::fromString($itemName);
			$tempItem->setCount(1);
            if ($this->m_Player instanceof Player) {
                if ($item == Kit::SLOT_KIT_HEAD)
                    $this->m_Player->getArmorInventory()->setHelmet($tempItem);
                if ($item == Kit::SLOT_KIT_CHEST)
                    $this->m_Player->getArmorInventory()->setChestplate($tempItem);
                if ($item == Kit::SLOT_KIT_PANTS)
                    $this->m_Player->getArmorInventory()->setLeggings($tempItem);
                if ($item == Kit::SLOT_KIT_BOOTS)
                    $this->m_Player->getArmorInventory()->setBoots($tempItem);
                if ($item == Kit::SLOT_KIT_HELD) {
                    $this->m_Player->getInventory()->setItemInHand($tempItem);
                    if ($itemName == "BOW") {
                        $arrows = ItemFactory::fromString("arrow");
                        $arrows->setCount(10);
                        $this->m_Player->getInventory()->addItem(clone $arrows);
                    }
                }
            }
		}
		$this->clearKitItems();
		$this->syncKitItems();
	}
    public function getEmail()
    {
        return $this->m_Email;
    }

    public function setLevel(int $p_level)
    {
        $this->m_Level = $p_level;
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "UPDATE players SET level = ? WHERE uuid = ?", [
                    ["s", $this->m_Level],
                    ["s", $this->getPlayer()->getUniqueId()]
                ]
            ));
    }

    public function setEmail(string $p_Email)
    {
        $this->m_Email = $p_Email;
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "UPDATE players SET email = ? WHERE uuid = ?", [
                    ["s", $this->m_Email],
                    ["s", $this->getPlayer()->getUniqueId()]
                ]
            ));
    }

    public function getLanguage(): int
    {
        return $this->m_Language;
    }

    public function setLanguage(int $p_Language): bool
    {
        if ($this->m_Language != $p_Language) {
            $this->m_Language = $p_Language;
            FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
                new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                    "UPDATE players SET lang = ? WHERE uuid = ?", [
                        ["i", $this->m_Language],
                        ["s", $this->getPlayer()->getUniqueId()]
                    ]
                ));
            FatUtils::getInstance()->getServer()->getPluginManager()->callEvent(new LanguageUpdatedEvent($this->getPlayer(), $this->m_Language));
            return true;
        }
        return false;
    }

    public function getPermissionGroup()
    {
        return $this->m_permissionGroup;
    }

    public function setPermissionGroup(string $p_groupName)
    {
        $this->m_permissionGroup = $p_groupName;
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(), "UPDATE players SET permission_group = ? WHERE uuid = ?", [
            ["s", $this->m_permissionGroup],
            ["s", $this->getPlayer()->getUniqueId()]
        ]));
    }

    public function getFSAccount()
    {
        return $this->m_FSAccount;
    }

    public function setFSAccount(string $p_FSAccount)
    {
        $this->m_FSAccount = $p_FSAccount;
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "UPDATE players SET fsaccount = ? WHERE uuid = ?", [
                    ["s", $this->m_FSAccount],
                    ["s", $this->getPlayer()->getUniqueId()]
                ]
            ));
    }

    //--> MUTE
    /**
     * @param int|null $p_timeToMute if null is given, player is mute for 5 days
     */
    public function setMuted(int $p_timeToMute = null)
    {
        $l_ExpirationTimestamp = time();
        if (is_null($p_timeToMute))
            $l_ExpirationTimestamp += 60 * 60 * 24 * 5;
        else {
            $l_ExpirationTimestamp += $p_timeToMute;
        }
        $l_Result = MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
            "UPDATE players SET muted = ? WHERE uuid = ?", [
                ["i", $l_ExpirationTimestamp],
                ["s", $this->getPlayer()->getUniqueId()]
            ]);
        $this->m_MutedTimestamp = $l_ExpirationTimestamp;
    }

    public function isMuted(): bool
    {
        return $this->m_MutedTimestamp > time();
    }

    public function getMutedExpiration(): int
    {
        return intval($this->m_MutedTimestamp) - time();
    }


    //--> Shop & Slots
    public function isEquipped(ShopItem $p_ShopItem)
	{
		$l_ActualItemAtSlot = $this->getSlot($p_ShopItem->getSlotName());
		return $l_ActualItemAtSlot instanceof ShopItem && strcmp($l_ActualItemAtSlot->getKey(), $p_ShopItem->getKey()) == 0;
	}

    public function getSlot(string $slotName): ?ShopItem
    {
        if (array_key_exists($slotName, $this->m_slots))
            return $this->m_slots[$slotName];
        return null;
    }

	public function getSlots(): array
	{
		return $this->m_slots;
	}

	public function addLootbox(int $p_ammount)
    {
        $l_ammount = $p_ammount;
        $l_currentLootbox = $this->getPlayer()->getInventory()->getItem(7);
        if ($l_currentLootbox != null)
            $l_ammount += $l_currentLootbox->getCount();

        $l_lootBox = Item::get(ItemIds::IRON_AXE);
        $l_lootBox->setCustomName(TextFormat::GOLD . "FatVault");
        $l_lootBox->setCount($l_ammount);
        $this->getPlayer()->getInventory()->setItem(7, $l_lootBox);
        //$this->getPlayer()->sendMessage("add lootbox");
        echo("add lootbox \n");
    }

    public function addAmmountableBoughtShopItem(ShopItem $p_ShopItem, $p_spentFS = 0, $p_spentFG = 0, $p_nbr = 1)
    {
        $isBrought = false;
        $val = $p_nbr;

            foreach ($this->m_BoughtShopItems as $l_key => $l_BoughtShopItem)
            {
                if (substr($l_BoughtShopItem, 0, strlen("paintball.")) == "paintball."
                    || substr($l_BoughtShopItem, 0, strlen("lootbox")) == "lootbox")
                {
                    $l_BoughtShopItem = explode(" ", $l_BoughtShopItem);
                    if ($p_ShopItem->getKey() == $l_BoughtShopItem[0])
                    {
                        $val = intval($l_BoughtShopItem[1]) + $p_nbr;
                        $this->m_BoughtShopItems[$l_key] = $p_ShopItem->getKey() . " " . $val;

                        $isBrought = true;
                        break;
                    }
                }
            }

        if (!$isBrought)
        {
            $this->m_BoughtShopItems[] = $p_ShopItem->getKey() . " " . $val;
        }
        $this->updateBoughtItems();

        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "INSERT INTO shop_history (uuid, name, item, spentFS, spentFG) VALUES (?, ?, ?, ?, ?)", [
                    ["s", $this->getPlayer()->getUniqueId()],
                    ["s", $this->getPlayer()->getName()],
                    ["s", $p_ShopItem->getName()],
                    ["i", $p_spentFS],
                    ["i", $p_spentFG]
                ]
            ));
    }

    public function updateBoughtItems()
    {
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "UPDATE players SET shop_possessed = ? WHERE uuid = ?", [
                    ["s", json_encode($this->m_BoughtShopItems)],
                    ["s", $this->getPlayer()->getUniqueId()]
                ]));
    }

    public function addBoughtShopItem(ShopItem $p_ShopItem, $p_spentFS = 0, $p_spentFG = 0)
	{
        $this->m_BoughtShopItems[] = $p_ShopItem->getKey();
        $this->updateBoughtItems();

        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
                "INSERT INTO shop_history (uuid, name, item, spentFS, spentFG) VALUES (?, ?, ?, ?, ?)", [
                    ["s", $this->getPlayer()->getUniqueId()],
                    ["s", $this->getPlayer()->getName()],
                    ["s", $p_ShopItem->getName()],
                    ["i", $p_spentFS],
                    ["i", $p_spentFG]
                ]
            ));
    }

    public function removeFromShopItemStack(String $p_Key, int $p_ammount) : bool
    {
        foreach ($this->m_BoughtShopItems as $l_key => $l_BoughtShopItem)
        {
            echo ("l_BoughtShopItem = " . $l_BoughtShopItem . "\n");
            $l_tab = explode(" ", $l_BoughtShopItem);
            if ($l_tab[0] == $p_Key)
            {
                if (intval($l_tab[1]) <= 0)
                    return false;
                $this->m_BoughtShopItems[$l_key] = $l_tab[0] . " " .  intval($l_tab[1] - $p_ammount);
                return true;
            }
        }
        return false;
    }

	public function isBought(ShopItem $p_ShopItem)
	{
	    $l_value = $p_ShopItem->getKey();
	    if (substr($p_ShopItem->getKey(), 0, strlen("paintball.")) == "paintball.")
        {
            $l_value = explode(" ", $p_ShopItem->getKey())[0];

            foreach ($this->m_BoughtShopItems as $l_BoughtShopItem)
            {
                if (explode(" ", $l_BoughtShopItem)[0] == $l_value)
                    return true;
            }
            return false;
        }
		return array_search($l_value, $this->m_BoughtShopItems) !== false;
	}

	private function updateSqlEquippedSlot()
	{
		$l_EquippedItemKeys = [];

		foreach ($this->m_slots as $l_ShopItem)
		{
			if ($l_ShopItem instanceof ShopItem)
				$l_EquippedItemKeys[] = $l_ShopItem->getKey();
		}

		MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
			"UPDATE players SET shop_equipped = ? WHERE uuid = ?", [
				["s", json_encode($l_EquippedItemKeys)],
				["s", $this->getPlayer()->getUniqueId()]
			]);
	}

	public function clearKitItems()
	{
		unset($this->m_KitItems);
		$this->m_KitItems = array();
		// KEEP IN MIND that at this moment, it's not sync with database, you need to do it after you're done
		// changing kits items
	}

	public function emptySlot(string $slotName)
	{
		if (array_key_exists($slotName, $this->m_slots))
		{
			unset($this->m_slots[$slotName]);
			$this->updateSqlEquippedSlot();
        }
	}

	public function getKitItemAtSlot(string $p_kitSlot) : string
	{
		echo $this->m_KitItems[$p_kitSlot] . "\n";
		return "";
	}

	public function setKitItem(string $p_kitSlot, string $p_item) : bool
	{
		// by the end of this function, kit items are not sync with DB, you need to do it afterward
		switch ($p_kitSlot)
		{
			case Kit::SLOT_KIT_HEAD:
				$this->m_KitItems[Kit::SLOT_KIT_HEAD] = $p_item;
				break;
			case Kit::SLOT_KIT_CHEST:
				$this->m_KitItems[Kit::SLOT_KIT_CHEST] = $p_item;
				break;
			case Kit::SLOT_KIT_PANTS:
				$this->m_KitItems[Kit::SLOT_KIT_PANTS] = $p_item;
				break;
			case Kit::SLOT_KIT_BOOTS:
				$this->m_KitItems[Kit::SLOT_KIT_BOOTS] = $p_item;
				break;
			case Kit::SLOT_KIT_HELD:
				$this->m_KitItems[Kit::SLOT_KIT_HELD] = $p_item;
				break;
			default:
				return false;
		}
		return true;
	}

	public function syncKitItems()
	{
		MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
			"UPDATE players SET kit_items = ? WHERE uuid = ?", [
				["s", json_encode($this->m_KitItems)],
				["s", $this->getPlayer()->getUniqueId()]
			]);
	}

    public function setSlot(string $slotName, ShopItem $p_value, bool $p_SaveDb = true)
    {
    	if ($p_value == null)
    		$this->emptySlot($slotName);
    	else
		{
			if (array_key_exists($slotName, $this->m_slots)) {
				$this->m_slots[$slotName]->unequip();
			}
			$this->m_slots[$slotName] = $p_value;
			if ($p_SaveDb)
				$this->updateSqlEquippedSlot();
		}
    }

	private function reequipRawShopItems(?string $l_RawEquippedItem): void
	{
		if (is_string($l_RawEquippedItem) && strlen($l_RawEquippedItem) > 2)
		{
			$l_EquippedItemKeys = json_decode($l_RawEquippedItem);
			if (is_array($l_EquippedItemKeys))
				ShopManager::getInstance()->equipItems($this->getPlayer(), $l_EquippedItemKeys);
		}
	}

    //--> FatSilver & FatGold
	public function getFatsilver(): int
	{
		return $this->m_Fatsilver;
	}

	public function getFatgold(): int
	{
		return $this->m_Fatgold;
	}

	public function addFatsilver(int $p_Modifier)
	{
		$this->m_Fatsilver += $p_Modifier;
		MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
			"UPDATE players SET fatsilver = ? WHERE uuid = ?", [
				["i", $this->m_Fatsilver],
				["s", $this->getPlayer()->getUniqueId()]
			]);
	}

	public function addFatgold(int $p_Modifier)
	{
		$this->m_Fatgold += $p_Modifier;
		MysqlResult::executeQuery(LoadBalancer::getInstance()->connectMainThreadMysql(),
			"UPDATE players SET fatgold = ? WHERE uuid = ?", [
				["i", $this->m_Fatgold],
				["s", $this->getPlayer()->getUniqueId()]
			]);
	}

	public function updateName()
    {
        FatUtils::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(
            new DirectQueryMysqlTask(LoadBalancer::getInstance()->getCredentials(),
            "UPDATE players SET name = ? WHERE uuid = ?", [
                ["s", $this->getPlayer()->getName()],
                ["s", $this->getPlayer()->getUniqueId()]
            ]
        ));
    }

	public function setPreviewing(bool $p_Value)
	{
		$this->m_isPreviewing = $p_Value;
	}

	public function isPreviewing()
	{
		return $this->m_isPreviewing;
	}

	public function getVipRank()
    {
        $group = $this->getPermissionGroup();

        if ($group == "Hero")
            return 0;
        if ($group == "Titan")
            return 1;
        if ($group == "Legend")
            return 2;
        return -1;
    }

    public function isWithinDist(Vector3 $p_location, int $p_dist) : bool
    {
        if ($this->calcDist($p_location) > $p_dist)
            return false;
        return true;
    }

    public function calcDist(Vector3 $p_location) : int
    {
        $l_playerPos = $this->getPlayer()->getLocation()->asVector3();
        return sqrt(pow($l_playerPos->x - $p_location->x, 2) + pow($l_playerPos->y - $p_location->y, 2) + pow($l_playerPos->z - $p_location->z, 2));
    }

    public function getDataRelativeToContext()
    {
        return $this->m_dataRelativeToContext;
    }

    public function setDataRelativeToContext($p_val)
    {
        $this->m_dataRelativeToContext = $p_val;
    }

    public function getBoughtItems()
    {
        return $this->m_BoughtShopItems;
    }

    public function changeBoughtItemAmmount(String $p_key, int $p_ammount)
    {
        echo ("changeBoughtItemAmmount : \n");
        foreach ($this->m_BoughtShopItems as $id => $item)
        {
            $tab = explode(" ", $item);
            if (count($tab) > 1)
            {
                if (strcmp($tab[0], $p_key) == 0)
                    $this->m_BoughtShopItems[$id] = $tab[0] . " " . $p_ammount;
            }
        }
    }

    public function getCurrentHotbarItemSlot()
    {
        return $this->m_currentHotbarSlot;
    }

    public function setCurrentHotbarItemSlot(int $p_value)
    {
        $this->m_currentHotbarSlot = $p_value;
    }

    public function setRightXpLevel()
    {

    }

    public function updateXpAndLevel()
    {
        echo ("yo 1 " . time() . " \n");
        $l_player = $this->getPlayer();
        $l_player->setXpLevel($this->m_Level);

        $l_xpToReach = 20000;
        if ($this->m_Level < 20)
            $l_xpToReach = $this->m_Level * 1000;

        $l_currentXP = CustomEntries::getInstance()->getEntry("XP", $l_player);


        if ($l_currentXP > $l_xpToReach)
        {
            $l_player->setXpLevel($l_player->getXpLevel() + 1);
            $this->setLevel($l_player->getXpLevel());
            $this->addLootbox(1);
            ScoresManager::getInstance()->setGlobalXpValue($l_player->getUniqueId(), ($l_currentXP - $l_xpToReach));
            $l_xpToReach = 20000;
            if ($this->m_Level < 20)
                $l_xpToReach = $this->m_Level * 1000;        }

        $currentLevelPct = $l_currentXP / ($l_xpToReach / 100);

        $currentLevelPct /= 100;

        echo ("current xp : " . $l_currentXP . " / total needed : " . $l_xpToReach . " current pct : " . $currentLevelPct . "\n");
        if (($currentLevelPct) < 1)
            $l_player->setXpProgress($currentLevelPct);
        echo ("yo 2 " . time() . " \n");
    }
}
