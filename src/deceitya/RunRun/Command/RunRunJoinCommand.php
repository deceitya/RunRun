<?php

declare(strict_types=1);

namespace deceitya\RunRun\Command;

use CortexPE\Commando\BaseSubCommand;
use deceitya\RunRun\Main;
use deceitya\RunRun\Session\GameSession;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class RunRunJoinCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission('runrun.command.join');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage('プレイヤーのみ実行できるコマンドです。');

            return;
        }

        $session = Main::getInstance()->getSession();
        if ($session instanceof GameSession) {
            if ($session->getPhase() === GameSession::PHASE_INVITING) {
                if ($session->addPlayer($sender)) {
                    $sender->getServer()->broadcastMessage("[RunRun] {$sender->getName()}さんがセッションに参加しました。");
                } else {
                    $sender->sendMessage('[RunRun] 既にセッションに参加しています。');
                }
            } else {
                $sender->sendMessage('[RunRun] 参加できないフェーズです');
            }
        } else {
            $sender->sendMessage('[RunRun] セッションが開いていません。');
        }
    }
}
