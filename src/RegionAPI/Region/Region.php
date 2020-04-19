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

namespace RegionAPI\Region;

use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\Server;
use pocketmine\Player;
use RegionAPI\Utils\JSON;
use RegionAPI\Utils\Flags;
use RegionAPI\Utils\Selector;
use RegionAPI\Loader;

class Region {
    /** @var string */
    private $name;
    /** @var Vector3 */
    private $pos1;
    /** @var Vector3 */
    private $pos2;
    /** @var string */
    private $worldName;
    /** @var bool[string] */
    private $flags;
    /** @var bool */
    private $global;
    /** @var JSON */
    private $json;
    /** @var int */
    private $created;
    /** @var int */
    private $modified;
    /** @var bool[] */
    private $options;

    /**
     * @param JSON $save - Json class
     * @return Region
     */
    public static function fromSave(JSON $save): Region {
        $data = $save->getCache();

        if ($data === null) {
            throw \Exception('Can not read region data on null');
        } else {
            $pos1 = new Vector3($data['pos1'][0], $data['pos1'][1], $data['pos1'][2]);
            $pos2 = new Vector3($data['pos2'][0], $data['pos2'][1], $data['pos2'][2]);

            return new Region($data['name'], $pos1, $pos2, $data['world'], $data['global'], $data['flags'], $save);
        }
    }

    public static function isValid($data): bool {
        if ($data === null) {
            return false;
        }

        if (!is_array($data)) {
            return false;
        }

        $validKeyMap = ['name', 'flags', 'pos1', 'pos2', 'global', 'modified', 'created', 'world'];

        foreach ($validKeyMap as $validKey) {
            if (!isset($data[$validKey])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets the default flags
     * @return bool[string]
     */
    public static function getDefaultFlags(): array {
        return [
            "consume" => true,
            "pvp" => false,
            "health" => true,
            "invincible" => true, 
            "use" => false,
            "break" => false, 
            "place" => false, 
            "sleep" => false,
            "tnt" => false,
            "tntDamage" => false,
            "fire" => false,
            "trample" => false, 
            "pickupItems" => false, 
            "dropItems" => false, 
            "hunger" => false, 
            "sendMessage" => true, 
            "recieveMessage" => true, 
            "potions" => false, 
            "updateSkin" => true,
            "seeSelf" => true,
            "seeOthers" => true,
            "wheatTick" => false
        ];
    }

    public function __construct(string $name, Vector3 $pos1, Vector3 $pos2, string $worldName, bool $global = null, array $flags = null, JSON $json = null) {
        $this->name = $name;
        $this->pos1 = $pos1;
        $this->pos2 = $pos2;
        $this->worldName = $worldName;
        $this->flags = $flags ?? self::getDefaultFlags();
        $this->global = $global ?? false;
        $this->json = $json;

        if ($this->json === null) {
            if (Loader::getInstance() === null) {
                throw new \InvalidArgumentException('JSON must be an instance of RegionsAPI\Utils\JSON class');
            } else {
                $path = Loader::getInstance()->getDataFolder() . "/$name.json";
                $this->json = new JSON($path);
                $this->save();
            }
        }

        $this->created = $this->json->getCache()['created'] ?? time();
        $this->modified = $this->json->getCache()['modified'] ?? time();
        $this->options = $this->json->getCache()['options'] ?? [];
    }

    /**
     * Checks if a position is in the current region
     * @return Bool
     */
    public function isInRegion(Position $pos): bool {
        $worldName = ($pos->getLevel()) ? $pos->getLevel()->getName() : '';
        $minX = min($this->pos1->x, $this->pos2->x);
        $maxX = max($this->pos1->x, $this->pos2->x);
        $minY = min($this->pos1->y, $this->pos2->y);
        $maxY = max($this->pos1->y, $this->pos2->y);
        $minZ = min($this->pos1->z, $this->pos2->z);
        $maxZ = max($this->pos1->z, $this->pos2->z);

        if ($this->worldName !== $worldName) {
            return false;
        } else {
            if ($this->global) {
                return true;
            }
            return (($minX <= $pos->x) && ($maxX >= $pos->x) && ($minY <= $pos->y) && ($maxY >= $pos->y) && ($minZ<= $pos->z) && ($maxZ >= $pos->z));
        }
    }

    /**
     * Checks whether or not the position is global
     * @return Bool
     */
    public function isGlobal(): bool {
        return $this->global;
    }

    /**
     * Returns a world for the region.
     * @return Level|Null
     */
    public function getWorld(): ?Level {
        return Server::getInstance()->getLevelByName($this->worldName);
    }

    /**
     * Gets the world name
     * @return string
     */
    public function getWorldName(): string {
        return $this->worldName;
    }

    /**
     * Gets whether a flag is enabled or disabled
     * @return Bool|Null
     */
    public function getFlag(string $flag): ?bool {
        return $this->flags[$flag];
    }

    /**
     * Applys saved data flags.
     * @return void
     */
    public function applyFlags(array $flags): void {
        foreach ($flags as $flag=>$value) {
            $this->flags[$flag] = $vale;
        }

        $this->modified = time();
    }

    /**
     * Changes a flag value (DOES NOT SAVE REGION)
     */
    public function setFlag(string $flag, bool $value): ?bool {
        if (!in_array($flag, Flags::getValidFlags())) {
            return null;
        }

        $this->modified = time();
        $this->flags[$flag] = $value;
        return $value;
    }

    /**
     * Gets the flags for the region
     */
    public function getFlags(): array {
        return $this->flags;
    }

    /**
     * Gets the players in a region
     */
    public function getPlayers(): array {
        $level = $this->getWorld();
        if ($level === null) {
            return [];
        } else {
            $in_region = [];
            foreach ($level->getEntities() as $ent) {
                if (!($ent instanceof Player)) continue;
                if ($this->isInRegion($ent->getPosition())) {
                    $in_region[] = $ent;
                }
            }
            return $in_region;
        }
    }

    /**
     * Gets the reason for declining the action
     */
    public function getReason(): string {
        return "§c§lSYSTEM → §r§cYou can't do that here.";
    }

    /**
     * Gets the region name kek
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Saves the region
     */
    public function save(): void {
        $save = [
            "name" => $this->name,
            "created" => $this->created ?? time(),
            "modified" => $this->modified ?? time(),
            "flags" => $this->flags,
            "global" => $this->global,
            "pos1" => [$this->pos1->x, $this->pos1->y, $this->pos1->z],
            "pos2" => [$this->pos2->x, $this->pos2->y, $this->pos2->z],
            "world" => $this->worldName,
            "options" => $this->options ?? []
        ];

        $this->json->write($save);
        return;
    }

    /**
     * USE WITH CAUTION!
     */
    public function delete(): void {
        $this->json->delete();
        return;
    }
}