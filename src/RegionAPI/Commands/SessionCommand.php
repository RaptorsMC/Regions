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

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use RegionAPI\Utils\Session;
use RegionAPI\Utils\Selector;
use RegionAPI\Utils\Flags;
use RegionAPI\RegionManager;
use RegionAPI\Loader;

class SessionCommand extends Command {
    /** @var Loader */
    private $plugin;

    public function __construct(Loader $loader) {
        parent::__construct('rsession');
        $this->plugin = $loader;
        $this->description = "Create a region session or get information on your session.";
        $this->usageMessage = "/resssion";
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

        $session = (Selector::hasSession($sender->getName())) ? Selector::getSession($sender->getName()) : Selector::createSession($sender->getName());

        $sender->sendMessage("§aSession Details: ");
        $sender->sendMessage("§7Position 1: " . (($session->pos1) ?? "§cNot Selected."));
        $sender->sendMessage("§7Position 2: " . (($session->pos2) ?? "§cNot Selected."));
        $sender->sendMessage("§7Region Name: " . (($session->getRegion() !== null) ? $session->getRegion()->getName() : "§cNo region."));
        return true;
    }
}