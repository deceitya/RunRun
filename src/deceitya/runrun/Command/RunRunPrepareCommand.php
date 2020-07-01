<?php

declare(strict_types=1);

namespace deceitya\RunRun\Command;

use CortexPE\Commando\BaseSubCommand;
use deceitya\RunRun\Event\Session\StartPreparingEvent;
use pocketmine\command\CommandSender;

class RunRunPrepareCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission('runrun.command.prepare');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        (new StartPreparingEvent())->call();
    }
}
