<?php
declare(strict_types=1);
namespace MyPlot\subcommand;

use MyPlot\Plot;
use pocketmine\command\CommandSender;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class HomeSubCommand extends SubCommand
{
	/**
	 * @param CommandSender $sender
	 *
	 * @return bool
	 */
	public function canUse(CommandSender $sender) : bool {
		return ($sender instanceof Player) and $sender->hasPermission("myplot.command.home");
	}

	/**
	 * @param Player $sender
	 * @param string[] $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args) : bool {
		$selected = null;
		if(empty($args)) {
			$selected = $sender;
		    $selectedName = $sender->getName();
			$plotNumber = 1;
		}elseif(isset($args[0]) and ($selected = $this->getPlugin()->getServer()->getOfflinePlayer($args[0])->getPlayer() ?? $this->getPlugin()->getServer()->getOfflinePlayer($args[0])) !== null) {
			$selectedName = ctype_lower($selected->getName()) ? $args[0] : $selected->getName();
			if(!isset($args[1]) or !is_numeric($args[1]))
				return false;
			$plotNumber = (int) $args[1];
		}else{
			return false;
		}
		if($selected instanceof OfflinePlayer and !isset($args[2])) {
			$levelName = "";
		}else{
			$levelName = $args[2] ?? $selected->getLevel()->getFolderName();
		}
		$plots = $this->getPlugin()->getPlotsOfPlayer($selectedName, $levelName);
		if(empty($plots)) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("home.noplots"));
			return true;
		}
		if(!isset($plots[$plotNumber - 1])) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("home.notexist", [$plotNumber]));
			return true;
		}
		usort($plots, function(Plot $plot1, Plot $plot2) {
			if($plot1->levelName == $plot2->levelName) {
				return 0;
			}
			return ($plot1->levelName < $plot2->levelName) ? -1 : 1;
		});
		$plot = $plots[$plotNumber - 1];
		if($this->getPlugin()->teleportPlayerToPlot($sender, $plot)) {
			$sender->sendMessage($this->translateString("home.success", [$plot, $plot->levelName]));
		}else{
			$sender->sendMessage(TextFormat::RED . $this->translateString("home.error"));
		}
		return true;
	}
}