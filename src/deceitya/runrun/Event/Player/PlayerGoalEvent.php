<?php

declare(strict_types=1);

namespace deceitya\RunRun\Event\Player;

use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;

class PlayerGoalEvent extends PlayerEvent
{
    public function __construct(Player $player)
    {
        $this->player = $player;
    }
}
