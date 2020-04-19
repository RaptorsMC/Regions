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

class PositionCommand extends Command {
    /** @var Loader */
    private $plugin;

    public function __construct(Loader $loader) {
        parent::__construct('rpos');
        $this->plugin = $loader;
        $this->description = "";
        $this->usageMessage = "/rpos <number>";
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
            $sender->sendMessage('§e§lUsage:§r§e /rpos <number>');
            return true;
        } else {
            $session = (Selector::hasSession($sender->getName())) ? Selector::getSession($sender->getName()) : Selector::createSession($sender->getName());

            if ($args[0] === "1") {
                $session->pos1 = $sender->getPosition()->floor();
                $sender->sendMessage('§aSuccessfully set position 1: ' . $session->pos1);
            } else if ($args[0] === "2") {
                $session->pos2 = $sender->getPosition()->floor();
                $sender->sendMessage('§aSuccessfully set position 2: ' . $session->pos2);
            } else {
                $sender->sendMessage('§c§lError: §r§cPosition number is either too high or not numeric.');
            }
            
            return true;
        }
    }
}