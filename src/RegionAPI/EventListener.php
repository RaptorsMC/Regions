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
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
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
     * @param string $flag - Flag to check
     * @param bool $value - Value to check for
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
     * Start of entity events
     * 
     * Handles Invincibility
     * @param EntityDamageEvent $ev
     * @return void
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
     * @param EntityDamageByEntityEvent $ev
     * @return void
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
     * Handles natural regen flag
     * @param EntityRegainHealthEvent $ev
     * @return void
     */
    public function onEntityRegen(EntityRegainHealthEvent $ev): void {
        $entity = $ev->getEntity();

        if (!($entity instanceof Player)) return;

        foreach (self::getRegionsWithFlag('health', false) as $region) {
            if ($region->isInRegion($entity->getPosition())) {
                $ev->setCancelled(true);
                return;
            }
        }   
    }

    /**
     * Handles explosions (initiating them)
     * @param ExplosionPrimeEvent $ev
     * @return void
     */
    public function onPrime(ExplosionPrimeEvent $ev): void {
        $entity = $ev->getEntity();

        foreach (self::getRegionsWithFlag('tnt', false) as $region) {
            if ($region->isInRegion($entity->getPosition())) {
                $ev->setCancelled(true);
                return;
            }
        }
    }

    /**
     * Handles explosions (handling them)
     * @param EntityExplodeEvent $ev
     * @return void
     */
    public function onExplode(EntityExplodeEvent $ev): void {
        $entity = $ev->getEntity();

        foreach (self::getRegionsWithFlag('tnt', false) as $region) {
            if ($region->isInRegion($entity->getPosition())) {
                $ev->setCancelled(true);
                return;
            }
        }
    }

    /**
     * End of entity events
     * Start of block stuff
     * 
     * Handles growing blocks
     * @param BlockGrowEvent $ev
     * @return void
     */
    public function onGrownEvent(BlockGrowEvent $ev): void {
        $block = $ev->getBlock();

        foreach (self::getRegionsWithFlag('blockUpdates', false) as $region) {
            if ($region->isInRegion($block)) {
                $ev->setCancelled(true);
                return;
            }
        }
    }

    /**
     * Handles blocks decaying such as leaves
     * @param LeavesDecayEvent $ev
     * @return void
     */
    public function onLeavesDecay(LeavesDecayEvent $ev): void {
        $block = $ev->getBlock();

        foreach (self::getRegionsWithFlag('decay', false) as $region) {
            if ($region->isInRegion($block)) {
                $ev->setCancelled(true);
                return;
            }
        }
    }

    /**
     * Handles blocks decaying such as leaves
     * @param BlockUpdateEvent $ev
     * @return void
     */
    public function onBlockNeighboringChange(BlockUpdateEvent $ev): void {
        $block = $ev->getBlock();

        foreach (self::getRegionsWithFlag('blockUpdates', false) as $region) {
            if ($region->isInRegion($block)) {
                $ev->setCancelled(true);
                return;
            }
        }
    }

    /**
     * Handles blocks burning away by fire
     * @param BlockSpreadEvent $ev
     * @return void
     */
    public function onBlockSpread(BlockSpreadEvent $ev): void {
        $block = $ev->getBlock();

        foreach (self::getRegionsWithFlag('flow', false) as $region) {
            if ($region->isInRegion($block)) {
                $ev->setCancelled(true);
                return;
            }
        }
    }

    /**
     * Handles blocks burning away by fire
     * @param BlockBurnEvent $ev
     * @return void
     */
    public function onBlockBurned(BlockBurnEvent $ev): void {
        $block = $ev->getBlock();

        foreach (self::getRegionsWithFlag('fire', false) as $region) {
            if ($region->isInRegion($block)) {
                $ev->setCancelled(true);
                return;
            }
        }
    }
    
    /**
     * Handles block breaking
     * @param BlockBreakEvent $ev
     * @return void
     */
    public function onBreakBlock(BlockBreakEvent $ev): void {
        $player = $ev->getPlayer();
        $block = $ev->getBlock();

        foreach (self::getRegionsWithFlag('break', false) as $region) {
            if ($region->isInRegion($block)) {
                $ev->setCancelled(true);
                $player->sendTip($region->getReason());
                return;
            }
        }
    }
    
    /**
     * Handles block placing
     * @param BlockPlaceEvent
     * @return void
     */
    public function onPlaceBlock(BlockPlaceEvent $ev): void {
        $player = $ev->getPlayer();
        $block = $ev->getBlock();

        foreach (self::getRegionsWithFlag('place', false) as $region) {
            if ($region->isInRegion($block)) {
                $ev->setCancelled(true);
                $player->sendTip($region->getReason());
                return;
            }
        }
    }

    /**
     * End of block events
     * Start of player events
     * 
     * Handles hunger
     * @param PlayerExhaustEvent $ev
     * @return void
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
     * Handles players trying to sleep in a bed
     * @param PlayerBedEnterEvent $ev
     * @return void
     */
    public function onPlayerSleep(PlayerBedEnterEvent $ev): void {
        $player = $ev->getPlayer();

        foreach (self::getRegionsWithFlag('sleep') as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $player->sendTip($region->getReason());
                $ev->setCancelled(true);
                return;
            }
        }     
    }

    /**
     * Handles seeing a player
     * @param PlayerMoveEvent $ev
     * @return void
     */
    public function onPlayerMove(PlayerMoveEvent $ev): void {
        $player = $ev->getPlayer();
        $online = $this->plugin->getServer()->getOnlinePlayers();
        $shouldSee = true;

        foreach (self::getRegionsWithFlag('seeOthers') as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $players = $region->getPlayers();
                foreach ($players as $play) {
                    if ($play->getName() === $player->getName()) continue;
                    $player->showPlayer($play);
                }
            }
        }

        /**
         * can not see people
         */
        foreach (self::getRegionsWithFlag('seeOthers', false) as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $players = $region->getPlayers();
                foreach ($players as $play) {
                    if ($play->getName() === $player->getName()) continue;
                    $player->hidePlayer($play);
                    $shouldSee = false;
                }
            }
        }

        foreach (self::getRegionsWithFlag('pvp', false) as $region) {
            if ($region->isInRegion($player->getPosition())) {
                if ($player->getGenericFlag(Entity::DATA_FLAG_ONFIRE)) {
                    $player->setGenericFlag(Entity::DATA_FLAG_ONFIRE, 0);
                }
            }
        }

        if ($shouldSee) {
            foreach ($online as $p) {
                $player->showPlayer($p);
            }
        }
    }

    /**
     * Handles dropping of items
     * @param PlayerDropItemEvent
     * @return void
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

    /**
     * Handles interactions
     * @param PlayerInteractEvent $ev
     * @return void
     */
    public function onInteract(PlayerInteractEvent $ev): void {
        $player = $ev->getPlayer();

        foreach (self::getRegionsWithFlag('use', false) as $region) {
            if ($region->isInRegion($player->getPosition())) {
                $ev->setCancelled(true);
                #$player->sendTip($region->getReason());
                return;
            }
        }
    }

    /**
     * Handles water bucket placing
     * @param PlayerBucketEvent
     * @return void
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
}