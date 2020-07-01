<?php

declare(strict_types=1);

namespace deceitya\RunRun\Command;

use CortexPE\Commando\BaseSubCommand;
use deceitya\RunRun\Event\Session\StartGameEvent;
use pocketmine\command\CommandSender;

class RunRunStartCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission('runrun.command.start');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        (new StartGameEvent())->call();
    }
}
