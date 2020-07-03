<?php

declare(strict_types=1);

namespace deceitya\RunRun\Command;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;

class RunRunCommand extends BaseCommand
{
    protected function prepare(): void
    {
        $this->registerSubCommand(new RunRunJoinCommand('join', 'ゲームに参加する'));
        $this->registerSubCommand(new RunRunInviteCommand('invite', '招待開始'));
        $this->registerSubCommand(new RunRunOpenCommand('open', 'セッションを開く'));
        $this->registerSubCommand(new RunRunPrepareCommand('prepare', 'ゲーム準備'));
        $this->registerSubCommand(new RunRunStartCommand('start', 'ゲームスタート'));
        $this->setPermission('runrun.command');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
    }
}
