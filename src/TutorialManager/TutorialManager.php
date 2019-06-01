<?php
namespace TutorialManager;

use AbilityManager\AbilityManager;
use Navigation\Navigation;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use QuestManager\QuestManager;
use UiLibrary\UiLibrary;

class TutorialManager extends PluginBase {
    public const SPAWN_POINT = [0.5, 76, 1000.5, "ReWorld"];
    public const FIRST_JOIN = 0;
    public const FIRST_QUEST = 1;
    public const FIRST_MONSTER = 2;
    public const FIRST_SHOP = 3;
    public const FIRST_SKILL = 4;
    public const FIRST_STAT = 5;
    private static $instance = null;
    //public $pre = "§l§e[ §f시스템 §e]§r§e";
    public $pre = "§e•";
    public $npc = "§l§c〔 §r§f레이스 §l§c〕";

    public static function getInstance() {
        return self::$instance;
    }

    public function onLoad() {
        self::$instance = $this;
    }

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        @mkdir($this->getDataFolder());
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, ["player" => [], "isTutorialing" => []]);
        $this->data = $this->config->getAll();
        $this->data["isTutorialing"] = $this->data["isTutorialing"];
        $this->ui = UiLibrary::getInstance();
        $this->quest = QuestManager::getInstance();
        $this->navi = Navigation::getInstance();
        $this->ability = AbilityManager::getInstance();
    }

    public function onDisable() {
        $this->save();
    }

    public function save() {
        $this->config->setAll($this->data);
        $this->config->save();
    }

    public function check(Player $player, int $code) {
        if (isset($this->data["isTutorialing"][$player->getName()])) {
            if ($this->data["isTutorialing"][$player->getName()] == self::FIRST_JOIN && $this->data["isTutorialing"][$player->getName()] == $code) {
                unset($this->data["isTutorialing"][$player->getName()]);
                $this->Tutorial_2($player);
                return;
            } elseif ($this->data["isTutorialing"][$player->getName()] == self::FIRST_QUEST && $this->data["isTutorialing"][$player->getName()] == $code) {
                unset($this->data["isTutorialing"][$player->getName()]);
                $this->Tutorial_3($player);
                return;
            } elseif ($this->data["isTutorialing"][$player->getName()] == self::FIRST_MONSTER && $this->data["isTutorialing"][$player->getName()] == $code) {
                unset($this->data["isTutorialing"][$player->getName()]);
                $this->Tutorial_4($player);
                return;
            } elseif ($this->data["isTutorialing"][$player->getName()] == self::FIRST_SHOP && $this->data["isTutorialing"][$player->getName()] == $code) {
                unset($this->data["isTutorialing"][$player->getName()]);
                $this->Tutorial_5($player);
                return;
            } elseif ($this->data["isTutorialing"][$player->getName()] == self::FIRST_SKILL && $this->data["isTutorialing"][$player->getName()] == $code) {
                unset($this->data["isTutorialing"][$player->getName()]);
                $this->Tutorial_6($player);
                return;
            } elseif ($this->data["isTutorialing"][$player->getName()] == self::FIRST_STAT && $this->data["isTutorialing"][$player->getName()] == $code) {
                unset($this->data["isTutorialing"][$player->getName()]);
                array_push($this->data["player"], $player->getName());
                $this->save();
                $player->addTitle(" ", "{$this->npc}\n수고했어! 난 이정도까지만 알려줄게.\n나중에 또 도와줄테니 힘내!", 10, 30, 10);
                $this->getScheduler()->scheduleDelayedTask(
                        new class($this, $player) extends Task {
                            public function __construct(TutorialManager $plugin, Player $player) {
                                $this->plugin = $plugin;
                                $this->player = $player;
                            }

                            public function onRun($currentTick) {
                                $this->plugin->ability->Question_1($this->player);
                            }
                        }, 3 * 20);
                return;
            }
        }
    }

    public function Tutorial_2(Player $player) {
        $this->quest->udata[$player->getName()]["퀘스트 듣는중..."] = "on";
        $this->getScheduler()->scheduleRepeatingTask(
                new class($this, $player) extends Task {
                    public function __construct(TutorialManager $plugin, Player $player) {
                        $this->plugin = $plugin;
                        $this->player = $player;
                        $this->text = [
                                "잘했어!",
                                "그렇게 사람들의 부탁을 받는걸\n퀘스트라고 해.\n\n\n",
                                "부탁을 들어주면 테나나 아이템을\n받을수 있으니 들어주는것도 좋아.\n\n\n",
                                "그럼 토마스의 농장으로 가볼까?,\n길 안내를 띄워줄게!\n\n\n",
                                "퀘스트 정보는 화면 하단의\n1번 슬릇을 눌러 확인할 수 있어!\n\n\n",
                                "이걸 메뉴라고 하는데, 이 외에도\n다른 기능들이 있으니 찬찬히 살펴봐!\n\n\n",
                                "앗, 그리고 이 길 찾기는 아까의 메뉴에서\n이용할 수 있으니 참고해!\n\n\n",
                                "그럼 어서 빨리 가보자!\n\n\n\n"
                        ];
                        $this->count = count($this->text);
                        $this->a = 0;
                    }

                    public function onRun($currentTick) {
                        if (!$this->player->isOnline()) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                            return;
                        }
                        if ($this->count > 0) {
                            $this->player->addTitle(" ", $this->plugin->npc . "\n" . $this->text[$this->a], 10, 30, 10);
                            $this->a++;
                            $this->count--;
                        } elseif ($this->count <= 0) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->data["isTutorialing"][$this->player->getName()] = $this->plugin::FIRST_QUEST;

                            $name = "토마스의 농장";
                            $pos = explode(":", $this->plugin->navi->data["place"]["토마스의 농장"]);
                            if (isset($this->plugin->navi->isNavigating[$this->player->getId()])) {
                                unset($this->plugin->navi->isNavigating[$this->player->getId()]);
                                if (isset($this->plugin->navi->Navigator[$this->player->getId()]))
                                    $this->plugin->navi->Navigator[$this->player->getId()]->despawnFrom($this->player);
                                unset($this->plugin->navi->Navigator[$this->player->getId()]);
                                unset($this->plugin->navi->destination[$this->player->getId()]);
                            }
                            $pos = new \pocketmine\level\Position($pos[0], $pos[1], $pos[2], $this->plugin->getServer()->getLevelByName($pos[3]));
                            $this->plugin->navi->isNavigating[$this->player->getId()] = $pos;
                            $this->plugin->navi->destination[$this->player->getId()] = $name;
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                        }
                    }
                }, 60);
    }

    public function Tutorial_3(Player $player) {
        $this->quest->udata[$player->getName()]["퀘스트 듣는중..."] = "on";
        $this->getScheduler()->scheduleRepeatingTask(
                new class($this, $player) extends Task {
                    public function __construct(TutorialManager $plugin, Player $player) {
                        $this->plugin = $plugin;
                        $this->player = $player;
                        $this->text = [
                                "도착했네.\n\n\n\n",
                                "여기가 토마스의 농장이야.\n돼지, 닭, 소 등을 잡을수 있지!\n\n\n",
                                "여기 동물들을 잡으면\n전리품과 경혐치를 주니까 한번 잡아봐!\n\n\n",
                                "아까 토마스님이 주신 몽둥이로 잡아봐.\n무기가 없으면 때릴 수 없으니 주의해!\n\n\n",
                                "그리고 다 잡고나면\n토마스님을 다시 찾아가야해!\n\n\n",
                                "파이팅!\n\n\n\n"
                        ];
                        $this->count = count($this->text);
                        $this->a = 0;
                    }

                    public function onRun($currentTick) {
                        if (!$this->player->isOnline()) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                            return;
                        }
                        if ($this->count > 0) {
                            $this->player->addTitle(" ", $this->plugin->npc . "\n" . $this->text[$this->a], 10, 30, 10);
                            $this->a++;
                            $this->count--;
                        } elseif ($this->count <= 0) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->data["isTutorialing"][$this->player->getName()] = $this->plugin::FIRST_MONSTER;
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                        }
                    }
                }, 60);
    }

    public function Tutorial_4(Player $player) {
        $this->quest->udata[$player->getName()]["퀘스트 듣는중..."] = "on";
        $this->getScheduler()->scheduleRepeatingTask(
                new class($this, $player) extends Task {
                    public function __construct(TutorialManager $plugin, Player $player) {
                        $this->plugin = $plugin;
                        $this->player = $player;
                        $this->text = [
                                "첫 임무 완료를 축하해!\n\n\n\n",
                                "이제 상점 이용법을 알려줄게.\n\n\n\n",
                                "앞의 상인에게 말을 걸면\n상점을 이용할 수 있어.\n\n\n",
                                "원하는 아이템을 맨 아래, 가운데 칸으로 끌어오면 돼!\n\n\n\n",
                                "그리고 좌우의 버튼으로 수량을 선택하고,\n맨 오른쪽 버튼으로 구매하면돼!\n\n\n",
                                "웅크리고 말을 걸면\n전리품을 팔 수도 있으니 참고하고!\n\n\n",
                                "한번 이용해봐!\n\n\n\n"
                        ];
                        $this->count = count($this->text);
                        $this->a = 0;
                    }

                    public function onRun($currentTick) {
                        if (!$this->player->isOnline()) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                            return;
                        }
                        if ($this->count > 0) {
                            if ($this->a == 2) $this->player->teleport(new \pocketmine\math\Vector3(16, 87, 1016));
                            $this->player->addTitle(" ", $this->plugin->npc . "\n" . $this->text[$this->a], 10, 30, 10);
                            $this->a++;
                            $this->count--;
                        } elseif ($this->count <= 0) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->data["isTutorialing"][$this->player->getName()] = $this->plugin::FIRST_SHOP;
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                        }
                    }
                }, 60);
    }

    public function Tutorial_5(Player $player) {
        $this->quest->udata[$player->getName()]["퀘스트 듣는중..."] = "on";
        $this->getScheduler()->scheduleRepeatingTask(
                new class($this, $player) extends Task {
                    public function __construct(TutorialManager $plugin, Player $player) {
                        $this->plugin = $plugin;
                        $this->player = $player;
                        $this->text = [
                                "이번엔 스킬을 알려줄게!\n\n\n\n",
                                "1번 핫바 메뉴, 프로필을 선택하면\n스킬 항목이 나올거야.\n\n\n",
                                "퀵슬롯 메뉴에서 핫바에 지정할\n스킬을 등록할 수 있어.\n\n\n",
                                "지정한 후, 해당 핫바를 선택하면\n스킬이 시전 돼!\n\n\n",
                                "그 아래 스킬트리에서는\n너의 스킬레벨을 관리할 수 있어.\n\n\n",
                                "스킬포인트는 레벨업당 2p씩 얻고,\n한번 올리면 되돌릴 수 없으니 신중해야 해!\n\n\n",
                                "한번 스킬을 시전해봐!\n\n\n\n"
                        ];
                        $this->count = count($this->text);
                        $this->a = 0;
                    }

                    public function onRun($currentTick) {
                        if (!$this->player->isOnline()) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                            return;
                        }
                        if ($this->count > 0) {
                            $this->player->addTitle(" ", $this->plugin->npc . "\n" . $this->text[$this->a], 10, 30, 10);
                            $this->a++;
                            $this->count--;
                        } elseif ($this->count <= 0) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->data["isTutorialing"][$this->player->getName()] = $this->plugin::FIRST_SKILL;
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                        }
                    }
                }, 60);
    }

    public function Tutorial_6(Player $player) {
        $this->quest->udata[$player->getName()]["퀘스트 듣는중..."] = "on";
        $this->getScheduler()->scheduleRepeatingTask(
                new class($this, $player) extends Task {
                    public function __construct(TutorialManager $plugin, Player $player) {
                        $this->plugin = $plugin;
                        $this->player = $player;
                        $this->text = [
                                "이번엔 스탯이야!\n\n\n\n",
                                "1번 핫바 메뉴, 프로필을 선택하면\n스탯 항목이 나올거야.\n\n\n",
                                "스탯은 5가지의 스탯이 있는데,\n직업마다 주 스탯이 다르니 참고해!\n\n\n",
                                "레벨업당 스탯포인트는 5p씩 상승하고,\n되돌릴 수 없으시 신중해야 해!\n\n\n",
                                "한번 스탯을 올려봐!\n\n\n\n"
                        ];
                        $this->count = count($this->text);
                        $this->a = 0;
                    }

                    public function onRun($currentTick) {
                        if (!$this->player->isOnline()) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                            return;
                        }
                        if ($this->count > 0) {
                            $this->player->addTitle(" ", $this->plugin->npc . "\n" . $this->text[$this->a], 10, 30, 10);
                            $this->a++;
                            $this->count--;
                        } elseif ($this->count <= 0) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->data["isTutorialing"][$this->player->getName()] = $this->plugin::FIRST_STAT;
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                        }
                    }
                }, 60);
    }

    public function isTutorialing(string $name) {
        return isset($this->data["isTutorialing"][$name]);
    }

    public function isTutorialed(string $name) {
        return in_array($name, $this->data["player"]);
    }

    public function Tutorial(Player $player) {

        $this->ability->Question_1($player);

        return true;
        if (isset($this->data["isTutorialing"][$player->getName()])) {
            if ($this->data["isTutorialing"][$player->getName()] == self::FIRST_JOIN)
                $this->Tutorial_1($player);
            elseif ($this->data["isTutorialing"][$player->getName()] == self::FIRST_QUEST)
                $this->Tutorial_2($player);
            elseif ($this->data["isTutorialing"][$player->getName()] == self::FIRST_MONSTER)
                $this->Tutorial_3($player);
            elseif ($this->data["isTutorialing"][$player->getName()] == self::FIRST_SHOP)
                $this->Tutorial_4($player);
            return;
        }
        $form = $this->ui->ModalForm(function (Player $player, array $data) {
            if ($data[0] == true) {
                $this->Tutorial_1($player);
            } else {
                array_push($this->data["player"], $player->getName());
                $this->save();
                $player->sendMessage("{$this->pre} 튜토리얼을 스킵하였습니다.");
                $this->ability->Question_1($player);
                return;
            }
        });
        $form->setTitle("Tele Tutorial");
        $form->setContent("\n§l§a▶ §r§f튜토리얼을 진행하시겠습니까?\n  스킵시, 다시 진행할 수 없습니다.");
        $form->setButton1("§l[예]");
        $form->setButton2("§l[아니오]\n§r§8다신 보지 않음");
        $form->sendToPlayer($player);
    }

    public function Tutorial_1(Player $player) {
        $this->quest->udata[$player->getName()]["퀘스트 듣는중..."] = "on";
        $this->getScheduler()->scheduleRepeatingTask(
                new class($this, $player) extends Task {
                    public function __construct(TutorialManager $plugin, Player $player) {
                        $this->plugin = $plugin;
                        $this->player = $player;
                        $this->text = [
                                "왔구나! 기다리고 있었어.\n\n\n\n",
                                "너가 여기에 온다는 소식을 듣고\n얼마나 기다렸는지..\n\n\n",
                                "여긴 토마스님의 저택이야.\n\n\n\n",
                                "아직은 여기 생활에 미숙한거 같으니\n내가 도와줄게.\n\n\n",
                                "토마스님이 부탁이 있다는데..\n\n\n\n",
                                "한번 클릭해서 말을 걸어봐!\n\n\n\n"
                        ];
                        $this->count = count($this->text);
                        $this->a = 0;
                    }

                    public function onRun($currentTick) {
                        if (!$this->player->isOnline()) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                            return;
                        }
                        if ($this->count > 0) {
                            $this->player->addTitle(" ", $this->plugin->npc . "\n" . $this->text[$this->a], 10, 30, 10);
                            $this->a++;
                            $this->count--;
                        } elseif ($this->count <= 0) {
                            unset($this->plugin->quest->udata[$this->player->getName()]["퀘스트 듣는중..."]);
                            $this->plugin->data["isTutorialing"][$this->player->getName()] = $this->plugin::FIRST_JOIN;
                            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                        }
                    }
                }, 60);
    }
}
