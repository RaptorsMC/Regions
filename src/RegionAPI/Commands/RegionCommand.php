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
namespace RegionAPI\Commands;

use pocketmine\level\Position;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use RegionAPI\Utils\Session;
use RegionAPI\Utils\Selector;
use RegionAPI\Utils\Flags;
use RegionAPI\RegionManager;
use RegionAPI\Region\Region;
use RegionAPI\Loader;

class RegionCommand extends Command {
    /** @var Loader */
    private $plugin;

    public function __construct(Loader $loader) {
        parent::__construct('region');
        $this->plugin = $loader;
        $this->description = "";
        $this->usageMessage = "/region <create/edit/remove/tp> <name> [flags]";
        $this->setPermission('RegionsAPI');
    }

    /**
     * @param CommandSender $sender - Command Sender
     * @param String $label - Label of the command
     * @param String[] $args - Arguments for command
     */
    public function execute(CommandSender $sender, string $label, array $args): bool {
        if (!$sender->hasPermission('RegionsAPI') && !$sender->isOp()) {
            $sender->sendMessage('You are either missing access to this command, or it does not exist.');
            return true;
        }

        if (!isset($args[0])) {
            $sender->sendMessage('§e§lUsage:§r§e /region <create/edit/info/list/remove> <name> [flags]');
            return true;
        } else {
            if ($args[0] === "list") {
                $regions = RegionManager::getRegions();
                $prettyr = array_map(function ($region) {
                    return "§8- §e" . $region->getName();
                }, $regions);

                if (empty($regions)) {
                    $sender->sendMessage('§c§lError:§r§c No regions exist.');
                    return true;
                } else {
                    $sender->sendMessage('§aListing all ' . count($regions) . ' regions: ');
                    $sender->sendMessage(implode("\n", $prettyr));
                    return true;
                }
            }

            if (!isset($args[1])) {
                $sender->sendMessage('§e§lUsage:§r§e /region <create/edit/info/list/remove> <name> [flags]');
                return true;
            }

            if ($args[0] === 'create') {
                $session = Selector::getSession($sender->getName());
                $regionName = $args[1];
                $regionFlags = (!isset($args[2])) ? Region::getDefaultFlags() : Flags::parse(array_slice($args, 2));

                if (!($session instanceof Session)) {
                    $sender->sendMessage('§c§lError: §r§cCreate a region session! Use /rpos or /rsession to create one.');
                    return true;
                }

                if (RegionManager::getRegion($regionName) !== null) {
                    $sender->sendMessage('§c§lError: §r§cRegion name exists.');
                    return true;
                }

                if ($session->pos1 === null || $session->pos2 === null) {
                    $sender->sendMessage('§c§lError: §r§cYou need to select positions! Use /rsession for info.');
                    return true;
                }

                RegionManager::createRegion($regionName, $sender->getLevel()->getName(), $session->pos1, $session->pos2, $regionFlags);
                $sender->sendMessage('§aSuccessfully created region: ' . $regionName);
                return true;
            } else if ($args[0] === 'edit' || $args[0] === "modify") {
                $session = Selector::getSession($sender->getName());
                $regionName = $args[1];

                if (RegionManager::getRegion($regionName) === null) {
                    $sender->sendMessage('§c§lError: §r§cRegion does not exist.');
                    return true;
                }

                if (!($session instanceof Session)) {
                    Selector::createSession($sender->getName());
                }

                $session = Selector::getSession($sender->getName());
                $session->setRegion(RegionManager::getRegion($regionName));
                $region = RegionManager::getRegion($regionName);
                $modified = [];

                if (isset($args[2])) {
                    $presentFlags = Flags::getPresentFlagKeys(array_slice($args, 2));

                    foreach ($presentFlags as $flag) {
                        $value = isset($region->getFlags()[$flag]) ? (!$region->getFlags()[$flag]) : false;
                        $region->setFlag($flag, $value);
                        $modified[] = "§8- §e$flag: §c" .  ((!$value) ? "§aon" : "§coff") . " §7-> " .(($value) ? "§aon" : "§coff");
                    }

                    $sender->sendMessage("§aSuccessfully modified region: $regionName!\n§7Changed Values:\n" . implode("\n", $modified));
                    return true;
                } else {
                    $sender->sendMessage('§c§lError: §r§cForm UI soon.');
                    return true;
                }
            } else if ($args[0] === "tp") {
                $regionName = $args[1];

                if (RegionManager::getRegion($regionName) === null) {
                    $sender->sendMessage('§c§lError: §r§cRegion name does not exist.');
                    return true;
                } else {
                    // TO DO: Prompt confirmation.
                    $region = RegionManager::getRegion($regionName);
                    $pos1 = $region->getPos1();
                    $pos2 = $region->getPos2();
                    $center = new Position(($pos1->getX() + $pos2->getX()) / 2, ($pos1->getY() + $pos2->getY()) / 2, ($pos1->getZ() + $pos2->getZ()) / 2, $region->getWorld());
                    $sender->teleport($center);
                    $sender->sendMessage('§aTeleported to Region "'. $regionName . '" successfully.');
                    return true;
                }
            } else if ($args[0] === 'remove') {
                $regionName = $args[1];

                if (RegionManager::getRegion($regionName) === null) {
                    $sender->sendMessage('§c§lError: §r§cRegion name does not exist.');
                    return true;
                } else {
                    // TO DO: Prompt confirmation.
                    RegionManager::deleteRegion($regionName);
                    $sender->sendMessage('§aRegion "'. $regionName . '" deleted successfully.');
                    return true;
                }
            } else if ($args[0] === 'info') {
                $regionName = $args[1];

                if (RegionManager::getRegion($regionName) === null) {
                    $sender->sendMessage('§c§lError: §r§cRegion name does not exist.');
                    return true;
                } else {
                    $region = RegionManager::getRegion($regionName);
                    $flags = [];
                    
                    foreach ($region->getFlags() as $name=>$flag) {
                        $flags[] = "§8 - §e$name: §c" .  (($flag === true) ? "§aon" : "§coff");
                    }

                    $sender->sendMessage('§aRegion info for: ' . $region->getName());
                    $sender->sendMessage("§6Flags:\n" . implode("\n", $flags));
                    $sender->sendMessage("§6World: §e" . $region->getWorldName());
                    return true;
                }
            } else {
                $sender->sendMessage('§e§lUsage:§r§e /region <create/modify/list/remove> <name> [flags]');
                return true;
            }
        }
    }
}