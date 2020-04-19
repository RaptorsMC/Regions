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

use pocketmine\plugin\PluginBase;
use RegionAPI\Commands\RegionCommand;
use RegionAPI\Commands\PositionCommand;
use RegionAPI\Commands\SessionCommand;

class Loader extends PluginBase {
    /** @var RegionManager */
    private $regionManager;
    /** @var EventListener */
    private $events;
    /** @var Loader */
    private static $instance;

    /**
     * @return void
     */
    public function onEnable(): void {
        self::$instance = $this;
        $this->regionManager = new RegionManager($this);
        $this->events = new EventListener($this);
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->registerAll('RegionAPI', [
            new RegionCommand($this),
            new PositionCommand($this),
            new SessionCommand($this)
        ]);
    }

    /**
     * @return void
     */
    public function onDisable(): void {
        self::$instance = null;
        RegionManager::saveAll(true);
    }

    public static function getInstance(): ?Loader {
        return self::$instance;
    }
}
