<?php
namespace Primus\CrateKeys\event;

use pocketmine\block\Chest;
use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\tile\Tile;

class PlayerCrateKeyUseEvent extends PlayerEvent implements Cancellable{
	
	public static $handlerList = null;
	public static $eventPool = [];
	public static $nextEvent = 0;

	/** @var Player $player */
	protected $player;
	/** @var  Chest $target */
	protected $target;
	/** @var Item $key */
	protected $key;
	
	public function __construct(Player $player, Item $key, Chest $target){
		$this->player = $player;
		$this->key = $key;
		$this->target = $target;
	}
	
	public function getPlayer() : Player {
		return $this->player;
	}
	
	public function getKey() : Item {
		return $this->key;
	}
	
	public function getTarget() : Chest {
		return $this->target;
	}
	
}