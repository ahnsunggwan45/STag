<?php

namespace ojy\stag;

use ojy\stag\cmd\AddTagCommand;
use ojy\stag\cmd\ListTagCommand;
use ojy\stag\cmd\RemoveTagCommand;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use ssss\utils\SSSSUtils;

class STag extends PluginBase
{

    /** @var STag */
    public static $instance;

    /** @var Config */
    public static $data;

    /** @var Tag[] */
    public static $tags = [];

    public function onLoad()
    {
        self::$instance = $this;
    }

    public function onEnable()
    {
        self::$data = new Config($this->getDataFolder() . "Data.yml", Config::YAML, []);

        foreach (self::$data->getAll() as $data) {
            $tag = Tag::deserialize($data);
            self::$tags[$tag->getPositionString()] = $tag;
        }

        new EventListener();

        foreach ([AddTagCommand::class, RemoveTagCommand::class, ListTagCommand::class] as $c)
            Server::getInstance()->getCommandMap()->register("STag", new $c);

        /*$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void {
            foreach (Server::getInstance()->getOnlinePlayers() as $p) self::tagUpdate($p, 22);
        }), 10);*/
    }

    public static function save()
    {
        $data = [];

        foreach (array_values(self::$tags) as $tag)
            $data[] = $tag->serialize();

        self::$data->setAll($data);
        self::$data->save();
    }

    public function onDisable()
    {
        self::save();
    }

    /**
     * @param Position $position
     * @param string $text
     * @return bool
     */
    public static function addTag(Position $position, string $text): bool
    {
        $posString = SSSSUtils::posToString($position);
        if (!isset(self::$tags[$posString])) {
            $tag = new Tag($text, $posString);
            self::$tags[$posString] = $tag;
            return true;
        }
        return false;
    }

    /**
     * @param Player $player
     * @param int $distance
     */
    public static function tagUpdate(Player $player, int $distance)
    {
        foreach (self::$tags as $posString => $tag) {
            if ($tag->getFolderName() !== $player->level->getFolderName()) continue;
            if ($tag->getPosition() instanceof Position) {
                if ($tag->getPosition()->distance($player->getPosition()) <= $distance) {
                    $tag->sendTag($player);
                } else {
                    $tag->removeTag($player);
                }
            } else {
                unset(self::$tags[$posString]);
            }
        }
    }

    /**
     * @param string $worldName
     * @return Tag[]
     */
    public static function getTagsByWorld(string $worldName): array
    {
        $res = [];
        foreach (array_values(self::$tags) as $tag)
            if ($tag->getPosition() instanceof Position) {
                if ($tag->getPosition()->getLevel()->getFolderName() === $worldName) {
                    $res[] = $tag;
                }
            }
        return $res;
    }
}