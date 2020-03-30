<?php

namespace ojy\stag;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\UUID;
use ssss\utils\SSSSUtils;

class Tag
{

    /** @var int */
    protected $id = -2;

    /** @var string */
    protected $text = "";

    /** @var string */
    protected $position = "";

    /** @var AddPlayerPacket */
    protected $sendPk;

    /** @var RemoveActorPacket */
    protected $removePk;

    /** @var array */
    protected $send = [];

    /**
     * Tag constructor.
     * @param string $text
     * @param string $position
     */
    public function __construct(string $text, string $position)
    {
        $this->text = $text;
        $this->position = $position;

        $this->sendPk = new AddPlayerPacket();
        $id = Entity::$entityCount++;
        $this->id = $id;
        $this->sendPk->entityRuntimeId = $id;
        $this->sendPk->entityUniqueId = $id;
        $this->sendPk->position = $this->getPosition()->add(0.5, 0.2, 0.5);
        $this->sendPk->username = str_replace("(줄바꿈)", "\n", $this->text);
        $this->sendPk->uuid = UUID::fromRandom();
        $this->sendPk->item = ItemFactory::get(Item::AIR, 0, 0);
        $flags = (1 << Entity::DATA_FLAG_IMMOBILE);
        $this->sendPk->metadata = [
            Entity::DATA_FLAGS => [
                Entity::DATA_TYPE_LONG,
                $flags
            ],
            Entity::DATA_SCALE => [
                Entity::DATA_TYPE_FLOAT,
                0.01
            ]
        ];


        $this->removePk = new RemoveActorPacket();
        $this->removePk->entityUniqueId = $id;
        $this->folderName = $this->getPosition()->level->getFolderName();
    }

    public $folderName = "";

    public function getFolderName(): string
    {
        return $this->folderName;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [$this->text, $this->position];
    }

    /**
     * @param array $data
     * @return Tag
     */
    public static function deserialize(array $data): self
    {
        return new self(...$data);
    }

    /**
     * @return Position
     */
    public function getPosition(): ?Position
    {
        return SSSSUtils::strToPosition($this->position);
    }

    /**
     * @return string
     */
    public function getPositionString(): string
    {
        return $this->position;
    }

    /**
     * @param Player $player
     */
    public function sendTag(Player $player)
    {
        if (!isset($this->send[$player->getName()])) {
            $this->send[$player->getName()] = true;
            $player->sendDataPacket(clone $this->sendPk);
        }
    }

    /**
     * @param Player $player
     */
    public function removeTag(Player $player)
    {
        if (isset($this->send[$player->getName()])) {
            unset($this->send[$player->getName()]);
            $player->sendDataPacket(clone $this->removePk);
        }
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
        foreach ($this->send as $playerName => $bool) {
            $player = Server::getInstance()->getPlayerExact($playerName);
            if ($player !== null) {
                $this->removeTag($player);
                $this->sendTag($player);
            } else {
                unset($this->send[$playerName]);
            }
        }
        /*foreach ($this->getPosition()->getLevel()->getPlayers() as $player) {
            $this->removeTag($player);
            $this->sendTag($player);
        }*/
    }
}