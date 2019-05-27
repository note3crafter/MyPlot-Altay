<?php
declare(strict_types=1);
namespace MyPlot;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseCommand;
use MyPlot\subcommand\AddHelperSubCommand;
use MyPlot\subcommand\AutoSubCommand;
use MyPlot\subcommand\BiomeSubCommand;
use MyPlot\subcommand\ClaimSubCommand;
use MyPlot\subcommand\ClearSubCommand;
use MyPlot\subcommand\DenyPlayerSubCommand;
use MyPlot\subcommand\DisposeSubCommand;
use MyPlot\subcommand\GenerateSubCommand;
use MyPlot\subcommand\GiveSubCommand;
use MyPlot\subcommand\HelpSubCommand;
use MyPlot\subcommand\HomesSubCommand;
use MyPlot\subcommand\HomeSubCommand;
use MyPlot\subcommand\InfoSubCommand;
use MyPlot\subcommand\ListSubCommand;
use MyPlot\subcommand\MiddleSubCommand;
use MyPlot\subcommand\NameSubCommand;
use MyPlot\subcommand\PvpSubCommand;
use MyPlot\subcommand\RemoveHelperSubCommand;
use MyPlot\subcommand\ResetSubCommand;
use MyPlot\subcommand\SetOwnerSubCommand;
use MyPlot\subcommand\SubCommand;
use MyPlot\subcommand\UnDenySubCommand;
use MyPlot\subcommand\WarpSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class Commands extends BaseCommand implements PluginIdentifiableCommand
{
	/** @var MyPlot $plugin */
	private $plugin;
	/** @var SubCommand[] $subCommands */
	private $subCommands = [];
	/** @var SubCommand[] $aliasSubCommands */
	private $aliasSubCommands = [];

	/**
	 * Commands constructor.
	 *
	 * @param MyPlot $plugin
	 */
	public function __construct(MyPlot $plugin) {
		parent::__construct($plugin->getLanguage()->get("command.name"));
		$this->plugin = $plugin;
		$this->setPermission("myplot.command");
		$this->setAliases([$plugin->getLanguage()->get("command.alias")]);
		$this->setDescription($plugin->getLanguage()->get("command.desc"));
		$this->setUsage($plugin->getLanguage()->get("command.usage"));
	}

	/**
	 * @return SubCommand[]
	 */
	public function getCommands() : array {
		return $this->subCommands;
	}

	/**
	 * @param SubCommand $command
	 */
	public function loadSubCommand(SubCommand $command) : void {
		$this->registerSubCommand($command);
		$this->subCommands[$command->getName()] = $command;
		if($command->getAlias() != "") {
			$this->aliasSubCommands[$command->getAlias()] = $command;
		}
	}

	/**
	 * @param string $name
	 */
	public function unloadSubCommand(string $name) : void {
		$subcommand = $this->subCommands[$name] ?? $this->aliasSubCommands[$name] ?? null;
		if($subcommand !== null) {
			unset($this->subCommands[$subcommand->getName()]);
			unset($this->aliasSubCommands[$subcommand->getAlias()]);
		}
	}

	/**
	 * This is where all the arguments, permissions, sub-commands, etc would be registered
	 */
	protected function prepare() : void {
		$plugin = $this->plugin;
		$this->loadSubCommand(new HelpSubCommand($plugin, "help", $this));
		$this->loadSubCommand(new ClaimSubCommand($plugin, "claim"));
		$this->loadSubCommand(new GenerateSubCommand($plugin, "generate"));
		$this->loadSubCommand(new InfoSubCommand($plugin, "info"));
		$this->loadSubCommand(new AddHelperSubCommand($plugin, "addhelper"));
		$this->loadSubCommand(new RemoveHelperSubCommand($plugin, "removehelper"));
		$this->loadSubCommand(new AutoSubCommand($plugin, "auto"));
		$this->loadSubCommand(new ClearSubCommand($plugin, "clear"));
		$this->loadSubCommand(new DisposeSubCommand($plugin, "dispose"));
		$this->loadSubCommand(new ResetSubCommand($plugin, "reset"));
		$this->loadSubCommand(new BiomeSubCommand($plugin, "biome"));
		$this->loadSubCommand(new HomeSubCommand($plugin, "home"));
		$this->loadSubCommand(new HomesSubCommand($plugin, "homes"));
		$this->loadSubCommand(new NameSubCommand($plugin, "name"));
		$this->loadSubCommand(new GiveSubCommand($plugin, "give"));
		$this->loadSubCommand(new WarpSubCommand($plugin, "warp"));
		$this->loadSubCommand(new MiddleSubCommand($plugin, "middle"));
		$this->loadSubCommand(new DenyPlayerSubCommand($plugin, "denyplayer"));
		$this->loadSubCommand(new UnDenySubCommand($plugin, "undenyplayer"));
		$this->loadSubCommand(new SetOwnerSubCommand($plugin, "setowner"));
		$this->loadSubCommand(new ListSubCommand($plugin, "list"));
		$this->loadSubCommand(new PvpSubCommand($plugin, "pvp"));
		$plugin->getLogger()->debug("Commands Registered to MyPlot");
	}

	/**
	 * @param CommandSender $sender
	 * @param string $aliasUsed
	 * @param BaseArgument[] $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if($this->getPlugin()->isDisabled()) {
			/** @noinspection PhpUndefinedMethodInspection */
			$sender->sendMessage($this->getPlugin()->getLanguage()->get("plugin.disabled"));
			return;
		}
		if(!isset($args[0])) {
			$args[0] = "help";
		}
		$subCommand = strtolower(array_shift($args));
		if(isset($this->subCommands[$subCommand])) {
			$command = $this->subCommands[$subCommand];
		}elseif(isset($this->aliasSubCommands[$subCommand])) {
			$command = $this->aliasSubCommands[$subCommand];
		}else{
			/** @noinspection PhpUndefinedMethodInspection */
			$sender->sendMessage(TextFormat::RED . $this->getPlugin()->getLanguage()->get("command.unknown"));
			return;
		}
		if($command->canUse($sender)) {
			if(!$command->execute($sender, $args)) {
				/** @noinspection PhpUndefinedMethodInspection */
				$usage = $this->getPlugin()->getLanguage()->translateString("subcommand.usage", [$command->getUsage()]);
				$sender->sendMessage($usage);
			}
		}else{
			/** @noinspection PhpUndefinedMethodInspection */
			$sender->sendMessage(TextFormat::RED . $this->getPlugin()->getLanguage()->get("command.unknown"));
		}
		return;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin() : Plugin {
		return $this->plugin;
	}
}