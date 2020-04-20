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

use RegionAPI\Region\Region;

class Flags {

    /**
     * @param array $flags
     */
    public static function parse(array $flags): array {
        $validFlags = self::getValidFlags();
        $flagAliases = self::getFlagAliases();
        $flaglist = [];

        foreach ($flags as $search) {
            foreach ($validFlags as $flag) {
                // check exact flag name
                if (strpos($search, "-{$flag}") !== false) {
                    $flaglist[$flag] = true;
                    continue;
                }

                $aliases = $flagAliases[$flag];

                // check aliases
                foreach ($aliases as $alias) {
                    if (strpos($search, "-{$alias}") !== false) {
                        $flaglist[$flag] = true;
                        break;
                    }
                }

                if (!isset($flaglist[$flag])) {
                    $flaglist[$flag] = false;
                }
            }
        }
        return $flaglist;
    }

    /**
     * Parses a string
     * @param string
     * @return string[]
     */
    public static function parseString(string $flags): array {
        return self::parse(explode(" ", $flags));
    }

    /**
     * Gets the present flag keys in a given array search for flags.
     * @return string[]
     */
    public static function getPresentFlagKeys(array $flags): array {
        $flags = self::parse($flags);
        return array_keys(array_filter($flags, function ($v) {
            return ($v);
        }));
    }

    /**
     * @return string[]
     */
    public static function getValidFlags(): array {
        return [
            "consume",
            "pvp",
            "health",
            "invincible", 
            "use",
            "break", 
            "place", 
            "sleep",
            "tnt",
            "fire",
            "trample", 
            "pickupItems", 
            "dropItems", 
            "hunger", 
            "sendMessage", 
            "recieveMessage", 
            "potions", 
            "updateSkin",
            "seeSelf",
            "seeOthers",
            "decay",
            "flow",
            "blockUpdates"
        ];
    }

    public static function getFlagAliases(): array {
        $possibilities = [
            "place" => [ "pl", "bu", "build" ],
            "break" => [ "br" ],
            "use" => [ "interact" ],
            "consume" => [ "eat" ],
            "pvp" => [ "attack", "hit" ],
            "health" => [ "regen", "natrualregen" ],
            "invincible" => [ "god", "superman" ],
            "sleep" => [ "rest" ],
            "tnt" => [ "explode", "explosions" ],
            "fire" => [ "burn" ],
            "trample" => [ "ruinCrops" ],
            "pickupItems" => [ "pickup" ],
            "dropItems" => [ "drop" ],
            "hunger" => [],
            "sendMessage" => [ "outsent", "sendMsg", "msg" ],
            "recieveMessage" => [ "insent", "getMsg", "msgr" ],
            "potions" => [ "pots", "pot" ],
            "updateSkin" => [ "changeSkin", "skin" ],
            "seeSelf" => [ "invis" ],
            "seeOthers" => [ "seeplayers", "hide", "hideplayers" ],
            "decay" => [],
            "flow" => [ "liquids", "spread" ],
            "blockUpdates" => []
        ];
        return $possibilities;
    }
}