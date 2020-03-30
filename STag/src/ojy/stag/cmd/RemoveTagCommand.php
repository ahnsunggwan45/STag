<?php

namespace ojy\stag\cmd;

use ojy\stag\STag;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\permission\Permission;

class RemoveTagCommand extends Command
{

    public function __construct()
    {
        parent::__construct("태그제거", "태그를 제거합니다.", "/태그제거 [번호]", ["태그삭제"]);
        $this->setPermission(Permission::DEFAULT_OP);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $prefix = "§l§b[알림] §r§7";
        if ($sender->hasPermission($this->getPermission())) {
            if (isset($args[0])) {
                $num = array_shift($args);
                $tags = array_keys(STag::$tags);
                if (isset($tags[$num])) {
                    $tag = STag::$tags[$tags[$num]];
                    $sender->sendMessage($prefix . "{$num}번 태그를 제거했습니다: {$tag->getText()}");
                    if ($tag->getPosition() instanceof Position) {
                        foreach ($tag->getPosition()->getLevel()->getPlayers() as $player) {
                            $tag->removeTag($player);
                        }
                    }
                    unset(STag::$tags[$tags[$num]]);
                } else {
                    $sender->sendMessage($prefix . "{$num}번 태그를 찾을 수 없습니다.");
                }
            }
        }
    }
}