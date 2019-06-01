<?php
namespace TutorialManager;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\SetLocalPlayerAsInitializedPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class EventListener implements Listener {

    public function __construct(TutorialManager $plugin) {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $ev) {
        $player = $ev->getPlayer();
        if (isset($this->plugin->data["isTutorialing"][$player->getName()]))
            unset($this->plugin->data["isTutorialing"][$player->getName()]);
        if (!in_array($player->getName(), $this->plugin->data["player"]))
            array_push($this->plugin->data["player"], $player->getName());
    }

    public function onPacketReceived(DataPacketReceiveEvent $ev) {
        $pk = $ev->getPacket();
        $player = $ev->getPlayer();
        if ($pk instanceof SetLocalPlayerAsInitializedPacket) {
            if (isset($this->plugin->data["isTutorialing"][$player->getName()]))
                unset($this->plugin->data["isTutorialing"][$player->getName()]);
            if (!in_array($player->getName(), $this->plugin->data["player"]))
                array_push($this->plugin->data["player"], $player->getName());
            /*if(!in_array($player->getName(), $this->plugin->data["player"])){
              $this->plugin->getScheduler()->scheduleDelayedTask(
                new class($this->plugin, $ev->getPlayer()) extends Task{
                  public function __construct(TutorialManager $plugin, Player $player){
                    $this->plugin = $plugin;
                    $this->player = $player;
                 }
                 public function onRun($currentTick){
                   $this->plugin->Tutorial($this->player);
                 }
              }, 3*20);
            }elseif(isset($this->plugin->data["isTutorialing"][$player->getName()])){
              $this->plugin->Tutorial($player);
            }*/
        }
    }

    public function onMove(PlayerMoveEvent $ev) {
        $player = $ev->getPlayer();
        if (isset($this->plugin->data["isTutorialing"][$player->getName()]) && $this->plugin->data["isTutorialing"][$player->getName()] == $this->plugin::FIRST_JOIN) {
            if ($player->distance(new Position(0.5, 76, 1000.5, $player->getServer()->getLevelByName("ReWorld"))) > 10) {
                $player->teleport(new Position(0.5, 76, 1000.5, $player->getServer()->getLevelByName("ReWorld")));
                if (!isset($this->plugin->quest->udata[$player->getName()]["퀘스트 듣는중..."]))
                    $player->addTitle(" ", "{$this->plugin->npc}\n토마스님께 말을 걸어봐!", 10, 30, 10);
            }
        }
        if (isset($this->plugin->data["isTutorialing"][$player->getName()]) && $this->plugin->data["isTutorialing"][$player->getName()] == $this->plugin::FIRST_QUEST) {
            if ($player->distance(new Position(-40, 65, 1308.5, $player->getServer()->getLevelByName("ReWorld"))) < 5) {
                $this->plugin->check($player, 1);
            }
        }
        if (isset($this->plugin->data["isTutorialing"][$player->getName()]) && $this->plugin->data["isTutorialing"][$player->getName()] == $this->plugin::FIRST_SHOP) {
            if ($player->distance(new Position(16, 87, 1016, $player->getServer()->getLevelByName("ReWorld"))) > 10) {
                $player->teleport(new Position(16, 87, 1016, $player->getServer()->getLevelByName("ReWorld")));
                if (!isset($this->plugin->quest->udata[$player->getName()]["퀘스트 듣는중..."]))
                    $player->addTitle(" ", "{$this->plugin->npc}\n상점을 한번 이용해봐!", 10, 30, 10);
            }
        }
    }

}
