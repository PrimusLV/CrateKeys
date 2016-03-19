<?php
namespace Primus\CrateKeys;

use pocketmine\event\Listener;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\item\Item;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\inventory\DoubleChestInventory;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\level\particle\SmokeParticle;

use Primus\CrateKeys\event\PlayerCrateKeyUseEvent;
use Primus\CrateKeys\event\PlayerCrateKeyRecieveEvent;

class EventListener implements Listener {
	
	protected $plugin;
	
	
	public function __construct (Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function onChestOpen(InventoryOpenEvent $e){
		if($e->isCancelled()) return;
		$inv = $e->getInventory();
		$p = $e->getPlayer();
		// IF TRYING TO OPEN WITH CRATE KEY
		if($p->getInventory()->getItemInHand()->getId() !== $this->plugin->getConfig()->get("key-id")) return;
		// CHECK Permission
		if(!$p->hasPermission("cratekeys.use")){
			$p->sendMessage($this->plugin->getLang('no-permission-for-use'));
			$e->setCancelled(true);
			return;
		} 
		
		// Disable DoubleChest inventory open with key
		if($inv instanceof DoubleChestInventory and $this->plugin->getConfig()->get("allow-use-keys-on-double-chests") === false){
		 	$e->setCancelled();
		 	$p->sendMessage($this->plugin->getLang('cant-open-double-chest'));
		     return true;
		 }
		 // ------------------
		$b = $inv->getHolder();
		$x = $b->getFloorX();
		$y = $b->getFloorY();
		$z = $b->getFloorZ();
		 // ------------------
		if($b->getLevel()->getBlockIdAt($x, $y - 1, $z) === $this->plugin->getConfig()->get("pattern-block-id") or $this->plugin->getConfig()->get('allow-keys-on-all-chests')){
	     if(empty($inv->getContents()) and $this->plugin->getConfig()->get('allow-occupied-chests') === false){
	     $this->plugin->getServer()->getPluginManager()->callEvent(new PlayerCrateKeyUseEvent($e->getPlayer(), $e->getPlayer()->getInventory()->getItemInHand(), $inv));
         return;
	     }else{
	     	$p->sendMessage($this->plugin->getLang('occupied-chest'));
	     	$e->setCancelled();
	     }
}else{
	$p->sendMessage($this->plugin->getLang("incorrect-pattern"));
	$e->setCancelled();
}
	}
	
	public function onMine(BlockBreakEvent $e){ 
		if($e->isCancelled()) return;
		$p = $e->getPlayer();
		$b = $e->getBlock();	
		if(array_key_exists($b->getId(),$this->plugin->getConfig()->get('source-blocks'))){
			if($this->plugin->getChance($this->plugin->getConfig()->get('chance'))){
			if(!$p->hasPermission('cratekeys.recieve')){
			$p->sendMessage($this->plugin->getLang('no-permission-for-recieve'));
			return;
		}
				$this->plugin->getServer()->getPluginManager()->callEvent(new PlayerCrateKeyRecieveEvent($player, $block));
         }
	  }
	}
	
	public function onKeyUse(PlayerCrateKeyUseEvent $e){
		if($e->isCancelled()) return;
		$p = $e->getPlayer();
		$key = $e->getKey();
		// Key--
		$pInv = $p->getInventory();
		$pInv->removeItem($key);
		$rKey = new Item($key->getId(), $key->getDamage(), $key->getCount() - 1);
		$pInv->addItem($rKey);
		// Load chest
		$this->plugin->putRandomContent($e->getTarget());
		$p->sendMessage($this->plugin->getLang("key-use-message",$p));
		// ADD PARTICLE
		$b = $e->getTarget()->getHolder();
		$particle = new SmokeParticle(new \pocketmine\math\Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()), $this->plugin->getConfig()->get('particle-scale'));
		$b->getLevel()->addParticle($particle);
	}
	
	public function onKeyRecieve(PlayerCrateKeyRecieveEvent $e){
		if($e->isCancelled()) return;
		$p = $e->getPlayer();
		$b = $e->getBlock();
		// DONT ALLOW GIVE KEYS TO CREATIVE PLAYERS
		if($p->getGamemode() == 1) return;
	    $p->sendMessage($this->plugin->getLang('key-recieve-message'));
	    if($this->plugin->getConfig()->get('broadcast-message-on-key-recieve')) $this->plugin->getServer()->broadcastMessage($this->plugin->getLang('key-recieve-broadcast-message'));
		// GIVE PLAYER KEY
		$key = Item::get($this->plugin->getConfig()->get('key-id'), 0 ,1);
		$e->getPlayer()->getInventory()->addItem($key);
		// ADD PARTICLE
		if($this->plugin->getConfig()->get('enable-key-recieve-particle')){
			$particle = new SmokeParticle(new \pocketmine\math\Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()), $this->plugin->getConfig()->get('particle-scale'));
			$p->getLevel()->addParticle($particle);
		}
	}
	
	public function onKeyHold(PlayerItemHeldEvent $e){
		if($e->isCancelled()) return;
		$i = $e->getItem();
		if($i->getId() === $this->plugin->getConfig()->get('key-id')){
			if($this->plugin->getConfig()->get('enable-custom-key-name'))$e->getPlayer()->sendPopup($this->plugin->getConfig()->get('key-popup'), 1);
		}
	}
	
	public function onPlace(BlockPlaceEvent $e){
		if($e->isCancelled()) return;
		$p= $e->getPlayer();
		$b = $e->getBlock();
		// TRYING TO CREATE CRATE CHEST
		if($b->getLevel()->getBlockIdAt($b->getFloorX(), $b->getFloorY() -1, $b->getFloorZ()) === $this->plugin->getConfig()->get('pattern-block-id') && $b->getId() === Block::CHEST){
		// CHECK PERMISSION
		if($p->hasPermission('cratekeys.chest.crate')){
				$p->sendMessage($this->plugin->getLang("crate-chest-created"));
				// ADD PARTICLE
				$particle = new SmokeParticle(new \pocketmine\math\Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()), $this->plugin->getConfig()->get('particle-scale'));
				$b->getLevel()->addParticle($particle);
				}else{
					$e->setCancelled();
					$p->sendMessage($this->plugin->getLang('no-permission-for-crate-chest-create'));
				}
		}
	}
	
}