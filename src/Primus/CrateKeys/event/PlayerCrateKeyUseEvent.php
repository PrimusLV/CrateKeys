<?php
namespace Primus\CrateKeys\event;

use pocketmine\event\player\PlayerEvent;
use pocketmine\inventory\DoubleChestInventory;
use pocketmine\inventory\ChestInventory;
use pocketmine\Player;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;

class PlayerCrateKeyUseEvent extends PlayerEvent implements Cancellable{
	
	public static $handlerList = null;
	public static $eventPool = [];
	public static $nextEvent = 0;
	
	protected $player;
	protected $target;
	protected $key;
	
	public function __construct(Player $player, Item $key, $target){
		$this->player = $player;
		$this->key = $key;
		$this->target = $target;
	}
	
	public function getPlayer(){
		return $this->player;
	}
	
	public function getKey(){
		return $this->key;
	}
	
	public function getTarget(){
		return $this->target;
	}
	
}