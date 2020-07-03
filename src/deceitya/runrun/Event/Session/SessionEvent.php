<?php

declare(strict_types=1);

namespace deceitya\RunRun\Event\Session;

use deceitya\RunRun\Session\GameSession;
use pocketmine\event\Event;

class SessionEvent extends Event
{
    /** @var GameSession */
    protected $session;

    public function __construct(GameSession $session)
    {
        $this->session = $session;
    }

    public function getSession(): GameSession
    {
        return $this->session;
    }
}
