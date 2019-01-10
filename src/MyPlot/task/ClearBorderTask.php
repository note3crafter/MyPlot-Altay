<?php
declare(strict_types=1);
namespace MyPlot\task;

use MyPlot\MyPlot;
use MyPlot\Plot;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class ClearBorderTask extends Task {
	/** @var MyPlot $plugin */
	private $plugin;
	private $plot, $level, $height, $plotWallBlock, $plotBeginPos, $xMax, $zMax, $maxBlocksPerTick, $pos;

	/**
	 * ClearPlotTask constructor.
	 *
	 * @param MyPlot $plugin
	 * @param Plot $plot
	 * @param int $maxBlocksPerTick
	 */
	public function __construct(MyPlot $plugin, Plot $plot, int $maxBlocksPerTick = 256) {
		$this->plugin = $plugin;
		$this->plot = $plot;
		$this->plotBeginPos = $plugin->getPlotPosition($plot);
		$this->level = $this->plotBeginPos->getLevel();
		$plotLevel = $plugin->getLevelSettings($plot->levelName);
		$plotSize = $plotLevel->plotSize;
		$this->xMax = $this->plotBeginPos->x + $plotSize;
		$this->zMax = $this->plotBeginPos->z + $plotSize;
		$this->height = $plotLevel->groundHeight;
		$this->plotWallBlock = $plotLevel->wallBlock;
		$this->maxBlocksPerTick = $maxBlocksPerTick;
		$this->pos = new Vector3($this->plotBeginPos->x, $this->height + 1, $this->plotBeginPos->z);
		$this->plugin = $plugin;
		$plugin->getLogger()->debug("Clear Border Task started at plot {$plot->X};{$plot->Z}");
	}

	public function onRun(int $currentTick) : void {
		foreach($this->level->getEntities() as $entity) {
			if($this->plugin->getPlotBB($this->plot)->isVectorInXZ($entity)) {
				if(!$entity instanceof Player) {
					$entity->flagForDespawn();
				}else{
					$this->plugin->teleportPlayerToPlot($entity, $this->plot);
				}
			}
		}
		$blocks = 0;
		while($this->pos->x < $this->xMax) {
			if($this->pos->x > $this->plotBeginPos->x and $this->pos->x < $this->xMax) { // make sure its only the border
				$this->pos->x++;
				continue;
			}
			while($this->pos->z < $this->zMax) {
				if($this->pos->z > $this->plotBeginPos->z and $this->pos->z < $this->zMax) { // make sure its only the border
					$this->pos->z++;
					continue;
				}
				while($this->pos->y <= $this->level->getWorldHeight()) {
					if($this->pos->y === $this->height + 1) {
						$block = $this->plotWallBlock;
					}else{
						$block = Block::get(Block::AIR);
					}
					$this->level->setBlock($this->pos, $block, false, false);
					$blocks++;
					if($blocks >= $this->maxBlocksPerTick) {
						$this->plugin->getScheduler()->scheduleDelayedTask($this, 1);
						return;
					}
					$this->pos->y++;
				}
				$this->pos->z++;
			}
			$this->pos->x++;
		}
		$this->plugin->getLogger()->debug("Clear Border task completed at {$this->plotBeginPos->x};{$this->plotBeginPos->z}");
	}
}