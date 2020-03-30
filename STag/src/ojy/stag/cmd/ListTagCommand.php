<?php

namespace ojy\stag\cmd;

use ojy\stag\STag;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;

class ListTagCommand extends Command
{

    public function __construct()
    {
        parent::__construct("태그목록", "태그 목록을 확인합니다.", "/태그목록 [페이지]");
        $this->setPermission(Permission::DEFAULT_OP);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission($this->getPermission())) {
            if (!isset($args[0])) $page = 1;
            else $page = (int)$args[0];
            $tags = array_keys(STag::$tags);
            $max = ceil(count($tags) / 5);
            if ($page > $max) $page = $max;
            $index1 = $page * 5 - 5;
            $index2 = $page * 5 - 1;
            $count = 0;
            $sender->sendMessage("====[{$page}/{$max}]====");
            foreach ($tags as $posString) {
                if ($index1 <= $count && $index2 >= $count) {
                    $tag = STag::$tags[$posString];
                    $str = mb_substr($tag->getText(), 0, 6, "UTF-8");
                    $sender->sendMessage("[{$count}] {$posString}: {$tag->getText()}");
                }

                ++$count;
            }
        }
    }
}