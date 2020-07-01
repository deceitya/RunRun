<?php

declare(strict_types=1);

namespace deceitya\RunRun\Command;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use deceitya\RunRun\Main;
use pocketmine\command\CommandSender;

class RunRunOpenCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument('course'));
        $this->setPermission('runrun.command.open');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (Main::getInstance()->createNewSession($args['course'])) {
            Main::getInstance()->getSession()->run();

            $sender->sendMessage('新しいセッションを開きました。');
        } else {
            $sender->sendMessage('既にセッションが開かれています。');
        }
    }
}
