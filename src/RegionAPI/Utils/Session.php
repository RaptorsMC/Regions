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
namespace RegionAPI\Utils;

use pocketmine\level\Position;
use RegionAPI\Utils\Session;
use RegionAPI\Region\Region;

class Session {
    /** @var Position */
    public $pos1;
    /** @var Position */
    public $pos2;
    /** @var string */
    private $player;
    /** @var Region */
    private $region;

    public function __construct(string $player) {
        $this->player = $player;
    }

    /**
     * Get the user for the session
     * @return string
     */
    public function getUser(): string {
        return $this->player;
    }

    /**
     * Add the region to the session (for editing)
     * @param Region $region
     * @return Region
     */
    public function setRegion(Region $region): Region {
        return $this->region = $region;
    }

    /**
     * Gets the region for the session.
     */
    public function getRegion(): ?Region {
        return $this->region;
    }

    public function clearRegion(): void {
        $this->region = null;
        return;
    }
}