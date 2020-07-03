<?php

declare(strict_types=1);

namespace deceitya\RunRun\Command;

use CortexPE\Commando\BaseSubCommand;
use deceitya\RunRun\Event\Session\StartInvitingEvent;
use deceitya\RunRun\Main;
use deceitya\RunRun\Session\GameSession;
use pocketmine\command\CommandSender;

class RunRunInviteCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission('runurn.command.invite');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $session = Main::getInstance()->getSession();
        if ($session instanceof GameSession) {
            (new StartInvitingEvent($session))->call();
        } else {
            $sender->sendMessage('[RunRun] セッションが開いていません。');
        }
    }
}
