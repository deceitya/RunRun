<?php

declare(strict_types=1);

namespace deceitya\RunRun\Particle;

use pocketmine\entity\Entity;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\Player;
use pocketmine\Server;

/**
 * 移動可能なFloatingTextParticle
 * これがのメインのネタ
 */
class MobileTextParticle extends FloatingTextParticle
{
    /**
     * 文字を更新
     *
     * @param array $players
     * @return void
     */
    public function updateText(array $players): void
    {
        $packet = new SetActorDataPacket();
        $packet->entityRuntimeId = $this->entityId;
        $packet->metadata = [
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title . ($this->text !== "" ? "\n" . $this->text : "")]
        ];

        Server::getInstance()->broadcastPacket($players, $packet);
    }

    /**
     * TextParticleを移動させる
     *
     * @param Player[] $players
     * @return void
     */
    public function updatePosition(array $players = []): void
    {
        $packet = new MovePlayerPacket();
        $packet->entityRuntimeId = $this->entityId;
        $packet->position = $this->asVector3();
        $packet->pitch = 0;
        $packet->yaw = 0;
        $packet->headYaw = 0;

        Server::getInstance()->broadcastPacket($players, $packet);
    }

    /**
     * 消す
     *
     * @param array $players
     * @return void
     */
    public function remove(array $players): void
    {
        $packet = new RemoveActorPacket();
        $packet->entityUniqueId = $this->entityId;

        Server::getInstance()->broadcastPacket($players, $packet);
    }
}
