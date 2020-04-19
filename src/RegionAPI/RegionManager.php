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

use pocketmine\math\Vector3;
use RegionAPI\Region\Region;
use RegionAPI\Utils\JSON;

class RegionManager {
    /** @var Loader */
    private $plugin;
    /** @var Loader */
    private static $loader;
    /** @var Region[] */
    private static $regions = [];

    public function __construct(Loader $loader) {
        $this->plugin = $loader;
        self::$loader = $loader;
        $this->loadSavedRegions();
    }

    /**
     * Loads all saved regions.
     * @return void
     */
    protected function loadSavedRegions(): void {
        $folder = $this->plugin->getDataFolder();
        $regions = array_filter(\scandir($folder), function ($value) {
            if (strpos($value, '.json') !== false) {
                return true;
            } else {
                return false;
            }
        });

        foreach ($regions as $regionFile) {
            $save = new JSON($folder . '/' . $regionFile, true);
            
            if (!Region::isValid($save->getCache())) {
                $this->plugin->getLogger()->debug("$regionFile is an invalid region.");
                continue;
            } else {
                $region = Region::fromSave($save);
                self::$regions[] = $region;
                $this->plugin->getLogger()->debug("$regionFile successfully loaded.");
            }
        }
    }

    /**
     * Gets all loaded regions.
     */
    public static function getRegions(): array {
        return self::$regions;
    }

    /** 
     * Gets a region by entered name.
     * @param String $name - Region to find by name
     */
    public static function getRegion(String $name): ?Region {
        foreach (self::$regions as $index=>&$region) {
            if ($region->getName() === $name) {
                return $region;
            }
        }
        return null;
    }

    public static function setRegion(Region $region): void {
        if (self::getRegion($region->getName()) === null) {
            self::$regions[] = $region;
        } else {
            foreach (self::$regions as $index=>$reg) {
                if ($reg->getName() === $region->getName()) {
                    unset(self::$regions[$index]);
                    self::$regions[] = $region;
                }
            }
        }
    }

    /**
     * @throws \Exception 
     */
    public static function createRegion(String $name, String $world, Vector3 $pos1, Vector3 $pos2, Array $flags = null): Region {
        if (self::getRegion($name)) {
            throw new \Exception('Region already exists');
        } else {
            $folder = Loader::getInstance()->getDataFolder();
            $path = $folder . "/$name.json";
            if (file_exists($path)) {
                throw new \Exception('Region already exists');
            } else {
                $save = new JSON($path);
                $save->write([
                    "name" => $name,
                    "created" => time(),
                    "modified" => -1,
                    "flags" => $flags ?? Region::getDefaultFlags(),
                    "global" => false,
                    "pos1" => [$pos1->x, $pos1->y, $pos1->z],
                    "pos2" => [$pos2->x, $pos2->y, $pos2->z],
                    "world" => $world,
                    "options" => [
                        "operators_bypass" => true
                    ]
                ]);

                return self::$regions[] = Region::fromSave($save);
            }
        }
    }

    /**
     * Deletes a region
     */
    public static function deleteRegion(string $name): bool {
        if (!self::getRegion($name)) {
            return false;
        } else {
            $region = self::getRegion($name);
            $region->delete();
            foreach (self::$regions as $index=>$reg) {
                if ($name === $reg->getName()) {
                    unset(self::$regions[$index]);
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * @param Bool $close - Whether to clear the region from memory. (Unload it)
     */
    public static function saveAll(Bool $close = false): void {
        foreach (self::$regions as $index=>$region) {
            $region->save();

            if ($close) {
                unset(self::$regions[$index]);
            }
        }
    }
}