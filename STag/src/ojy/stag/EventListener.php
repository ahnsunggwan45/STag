<?php

namespace ojy\stag;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;

class EventListener implements Listener
{

    public function __construct()
    {
        Server::getInstance()->getPluginManager()->registerEvents($this, STag::$instance);
    }

    /*public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $tags = STag::getTagsByWorld($player->getLevel()->getFolderName());
        foreach ($tags as $tag) {
            $tag->sendTag($player);
        }
    }*/

    /** @var TaskHandler[] */
    public static $tasks = [];

    public function onJoin(PlayerJoinEvent $event)
    {
        $pl = $event->getPlayer();
        $task = new ClosureTask(function (int $currentTick) use ($pl): void {
            STag::tagUpdate($pl, 22);
        });
        STag::$instance->getScheduler()->scheduleRepeatingTask($task, 10);
        self::$tasks[$pl->getName()] = $task->getHandler();
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        if (isset(self::$tasks[$event->getPlayer()->getName()])) {
            self::$tasks[$event->getPlayer()->getName()]->cancel();
            unset(self::$tasks[$event->getPlayer()->getName()]);
        }
    }

    public function onTeleport(EntityTeleportEvent $event)
    {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            $from = $event->getFrom();
            if ($from->level->getFolderName() !== $event->getTo()->level->getFolderName()) {
                $tags = STag::getTagsByWorld($from->getLevel()->getFolderName());
                foreach ($tags as $tag) {
                    $tag->removeTag($player);
                }
            }
            /*
                        $to = $event->getTo();
                        $tags = STag::getTagsByWorld($to->getLevel()->getFolderName());
                        foreach ($tags as $tag) {
                            $tag->sendTag($player);
                        }*/
        }
    }

    /*public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $tags = STag::getTagsByWorld($player->getLevel()->getFolderName());
        foreach ($tags as $tag) {
            $tag->removeTag($player);
        }
    }*/
}