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

use RegionAPI\Utils\Session;
use pocketmine\Player;

class Selector {
    /** @var Session[] */
    private static $sessions = [];
    /** @var Loader */
    private static $plugin;

    public function __construct(Loader $plugin) {
        self::$plugin = $plugin;
    }

    /**
     * Creates a selection session.
     * @param string $player
     */
    public static function createSession(string $player): ?Session {
        if (!self::hasSession($player)) {
            return self::$sessions[] = new Session($player);
        }
        return null;
    }

    /**
     * Returns whether a player has a session
     * @param string $player
     * @return bool
     */
    public static function hasSession(string $player): bool {
        return (self::getSession($player) === null) ? false : true;
    }

    /**
     * Returns a player session
     * @param string $player
     * @return bool
     */
    public static function getSession(string $player): ?Session {
        foreach (self::$sessions as $session) {
            if ($session->getUser() === $player) {
                return $session;
            }
        }
        return null;
    }
}