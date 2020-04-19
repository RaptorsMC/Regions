<?php
/***
 *      _____                _                      __  __   _____ 
 *     |  __ \              | |                    |  \/  | / ____|
 *     | |__) | __ _  _ __  | |_  ___   _ __  ___  | \  / || |     
 *     |  _  / / _` || '_ \ | __|/ _ \ | '__|/ __| | |\/| || |     
 *     | | \ \| (_| || |_) || |_| (_) || |   \__ \ | |  | || |____ 
 *     |_|  \_\\__,_|| .__/  \__|\___/ |_|   |___/ |_|  |_| \_____|
 *                   | |                                           
 *                   |_|                    
 * 
 * @author Bavfalcon9
 */

namespace RegionAPI;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerBucketEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;

class EventListener implements Listener {
    /** @var Loader */
    private $plugin;

    /**
     * Gets an array of regions that match the given flag.
     * @return Region[];
     */
    public static function getRegionsWithFlag(string $flag, bool $val = true): array {
        $regions = [];
        foreach (RegionManager::getRegions() as $region) {
            if ($region->getFlag($flag) === $val) {
                $regions[] = $region;
            }
        }
        return $regions;
    }

    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    /**
     * Handles Invincibility
     */
    public function onDamage(EntityDamageEvent $ev): void {
        $entity = $ev->getEntity();

        if (!($entity instanceof Player)) return;

        foreach (self::getRegionsWithFlag('invincible') as $region) {
            if ($region->isInRegion($entity->getPosition())) {
                $ev->setCancelled(true);
                return;
            }
        }
    }

    /**
     * Handles PVP
     */
    public function onHitEntity(EntityDamageByEntityEvent $ev): void {
        $entity = $ev->getDamager();

        if (!($entity instanceof Player)) return;

        foreach (self::getRegionsWithFlag('pvp') as $region) {
            if ($region->isInRegion($entity->getPosition())) {
                $ev->setCancelled(true);
                $entity->sendTip($region->getReason());
                return;
            }
        }
    }

    /**
     * Handles interactions
     */
    public function onInteract(PlayerInteractEvent $ev): void {
        $player = $ev->getPlayer();

        foreach (self::getRegionsWithFlag('use', false) as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $ev->setCancelled(true);
                $player->sendTip($region->getReason());
                return;
            }
        }
    }

    /**
     * Handles water bucket placing
     */
    public function onBucket(PlayerBucketEvent $ev): void {
        $player = $ev->getPlayer();

        foreach (self::getRegionsWithFlag('place', false) as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $ev->setCancelled(true);
                $player->sendTip($region->getReason());
                return;
            }
        }
    }
    
    /**
     * Handles block breaking
     */
    public function onBreakBlock(BlockBreakEvent $ev): void {
        $player = $ev->getPlayer();

        foreach (self::getRegionsWithFlag('break', false) as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $ev->setCancelled(true);
                $player->sendTip($region->getReason());
                return;
            }
        }
    }
    
    /**
     * Handles block placing
     */
    public function onPlaceBlock(BlockPlaceEvent $ev): void {
        $player = $ev->getPlayer();

        foreach (self::getRegionsWithFlag('place', false) as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $ev->setCancelled(true);
                $player->sendTip($region->getReason());
                return;
            }
        }
    }

    /**
     * Handles hunger
     */
    public function onFoodTick(PlayerExhaustEvent $ev): void {
        $player = $ev->getPlayer();

        foreach (self::getRegionsWithFlag('hunger', false) as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $ev->setCancelled(true);
                return;
            }
        }
    }

    /**
     * Handles seeing a player
     */
    public function onPlayerMove(PlayerMoveEvent $ev): void {
        $player = $ev->getPlayer();

        foreach (self::getRegionsWithFlag('seeOthers', true) as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $players = $region->getPlayers();
                foreach ($players as $play) {
                    if ($play->getName() === $player->getName()) continue;
                    $player->showPlayer($play);
                }
            }
        }

        foreach (self::getRegionsWithFlag('seeOthers', false) as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $players = $region->getPlayers();
                foreach ($players as $play) {
                    if ($play->getName() === $player->getName()) continue;
                    $player->hidePlayer($play);
                }
            }
        }
    }

    /**
     * Handles dropping of items
     */
    public function onDropItem(PlayerDropItemEvent $ev): void {
        $player = $ev->getPlayer();

        foreach (self::getRegionsWithFlag('dropItems', false) as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $ev->setCancelled();
                $player->sendTip($region->getReason());
                return;
            }
        }
    }
}