<?php

namespace ojy\stag\cmd;

use ojy\stag\STag;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\Player;
use ssss\utils\SSSSUtils;

class AddTagCommand extends Command
{

    public function __construct()
    {
        parent::__construct("태그생성", "태그를 생성합니다.", "/태그생성 [text]", ["태그추가"]);
        $this->setPermission(Permission::DEFAULT_OP);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $prefix = "§l§b[알림] §r§7";
        if ($sender instanceof Player) {
            if ($sender->hasPermission($this->getPermission())) {
                if (isset($args[0])) {
                    $text = implode(" ", $args);
                    if (STag::addTag($sender->getPosition(), $text)) {
                        $posString = SSSSUtils::posToString($sender->getPosition());
                        $id = array_search($posString, array_keys(STag::$tags)) ?? "unknown";
                        $sender->sendMessage($prefix . "{$id}번 태그를 생성했습니다: {$text}");
                    } else {
                        $sender->sendMessage($prefix . "그 자리에 이미 태그가 존재합니다.");
                    }
                }
            }
        }
    }
}