<?php

declare(strict_types=1);

namespace deceitya\RunRun\Command;

use CortexPE\Commando\BaseSubCommand;
use deceitya\RunRun\Event\Session\StartInvitingEvent;
use pocketmine\command\CommandSender;

class RunRunInviteCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission('runurn.command.invite');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        (new StartInvitingEvent())->call();
    }
}
