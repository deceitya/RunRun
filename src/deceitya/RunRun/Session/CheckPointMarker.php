<?php

declare(strict_types=1);

namespace deceitya\RunRun\Session;

use deceitya\RunRun\Particle\MobileTextParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;

/**
 * 指定した地点のマーカー
 * これがメインのネタ
 */
class CheckPointMarker extends Task
{
    /** @var Player */
    private $player;
    /** @var Vector3 */
    private $destination;
    /** @var MobileTextParticle */
    private $particle;

    public function __construct(Player $player, Vector3 $destination, MobileTextParticle $particle)
    {
        $this->player = $player;
        $this->destination = $destination;
        $this->particle = $particle;
    }

    public function onRun(int $currentTick)
    {
        $position = $this->player->add($this->destination
            ->subtract($this->player->asVector3())
            ->normalize()
            ->multiply(5))
            ->add(0, $this->player->getEyeHeight());
        $this->particle->setComponents($position->x, $position->y, $position->z);
        $this->particle->updatePosition([$this->player]);

        $this->particle->setText(floor($this->destination->distance($this->player->asVector3())) . "m");
        $this->particle->updateText([$this->player]);
    }

    public function setDestination(Vector3 $destination): void
    {
        $this->destination = $destination;
    }
}
