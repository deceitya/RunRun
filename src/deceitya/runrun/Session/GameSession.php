<?php

declare(strict_types=1);

namespace deceitya\RunRun\Session;

use deceitya\RunRun\Event\Player\PlayerGoalEvent;
use deceitya\RunRun\Event\Session\StartGameEvent;
use deceitya\RunRun\Event\Session\StartInvitingEvent;
use deceitya\RunRun\Event\Session\StartPreparingEvent;
use deceitya\RunRun\Main;
use deceitya\RunRun\Particle\MobileTextParticle;
use Generator;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

use function in_array;
use function flowy\listen;
use function flowy\start;

/**
 * セッション
 *
 * @author deceitya
 */
class GameSession
{
    // なんもない
    public const PHASE_VOID = 0;
    // プレイヤー招待中
    public const PHASE_INVITING = 1;
    // ゲーム準備中
    public const PHASE_PREPARING = 2;
    // ゲーム進行中
    public const PHASE_IN_GAME = 3;
    // ゲーム終了
    public const PHASE_END = 4;

    /** @var int */
    private $phase = GameSession::PHASE_VOID;

    /** @var string[] */
    private $players = [];
    /** @var array[] */
    private $checkPointData;

    public function __construct(array $checkPointData)
    {
        $this->checkPointData = $checkPointData;
    }

    /**
     * フェーズを取得する
     *
     * @return integer
     */
    public function getPhase(): int
    {
        return $this->phase;
    }

    /**
     * プレイヤーをセッションに追加する
     *
     * @param Player $player
     * @return boolean
     */
    public function addPlayer(Player $player): bool
    {
        if (in_array($player->getName(), $this->players, true)) {
            return false;
        }

        $this->players[] = $player->getName();

        return true;
    }

    /**
     * セッションにいるプレイヤー達取得
     *
     * @return array
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    /**
     * セッションフロー
     *
     * @return void
     */
    public function run(): void
    {
        $stream = start(Main::getInstance());
        $stream->run(function ($stream) {
            $server = Server::getInstance();

            yield listen(StartInvitingEvent::class);
            $this->phase = GameSession::PHASE_INVITING;
            $server->broadcastMessage('[RunRun] セッションに参加できるようになりました。');


            yield listen(StartPreparingEvent::class);
            $this->phase = GameSession::PHASE_PREPARING;
            $this->sendMessageToSessionPlayer('[RunRun] ゲームの準備中です。');

            $stats = [];
            $players = [];
            foreach ($this->players as $name) {
                $player = $server->getPlayer($name);
                if ($player instanceof Player) {
                    $pos = $this->checkPointData[0];
                    $player->teleport(new Position($pos['x'], $pos['y'], $pos['z'], $server->getLevelByName($pos['level'])));
                    $player->setImmobile(true);

                    $stats[$name] = 0;
                    $players[] = $player;
                }
            }


            yield listen(StartGameEvent::class);
            $this->phase = GameSession::PHASE_IN_GAME;

            foreach ($players as $player) {
                $this->playerGameFlow($player);
                $player->setImmobile(false);
            }
            $this->sendMessageToSessionPlayer('[RunRun] GO!');

            $rank = 1;
            while (true) {
                $event = yield listen(PlayerQuitEvent::class, PlayerGoalEvent::class);
                $player = $event->getPlayer();
                $name = $player->getName();
                if ($event instanceof PlayerQuitEvent) {
                    $this->sendMessageToSessionPlayer("{$name}さんがゲーム中に退出しました。");
                    $stats[$player->getName()] = -1;
                } elseif ($event instanceof PlayerGoalEvent) {
                    $this->sendMessageToSessionPlayer("{$name}さんが{$rank}位でゴールしました。");
                    $stats[$event->getPlayer()->getName()] = $rank;
                    $rank++;
                }

                foreach ($stats as $name => $stat) {
                    if ($stat === 0) {
                        continue 2;
                    }
                }

                break;
            }


            $this->phase = GameSession::PHASE_END;
            $this->sendMessageToSessionPlayer('[RunRun] ゲーム終了です。');

            asort($stats);
            foreach ($stats as $name => $rank) {
                if ($rank !== -1) {
                    $this->sendMessageToSessionPlayer("{$rank}位: {$name}");
                }
            }
        });
    }

    /**
     * チェックポイントを取得
     *
     * @return Position[]|Generator
     */
    private function getCheckPoints(): Generator
    {
        foreach ($this->checkPointData as $data) {
            yield new Position($data['x'], $data['y'], $data['z'], Server::getInstance()->getLevelByName($data['level']));
        }
    }

    /**
     * プレイヤーのゲームのフロー
     *
     * @param Player $player
     * @return void
     */
    private function playerGameFlow(Player $player): void
    {
        $stream = start(Main::getInstance());
        $stream->run(function ($stream) use ($player) {
            $particle = new MobileTextParticle($player->asVector3(), '');
            $player->level->addParticle($particle, [$player]);
            $marker = new CheckPointMarker($player, $player->add($player->getDirectionVector()), $particle);
            Main::getInstance()->getScheduler()->scheduleRepeatingTask($marker, 1);

            foreach ($this->getCheckPoints() as $checkPoint) {
                $marker->setDestination($checkPoint);
                yield listen(PlayerMoveEvent::class)->filter(
                    function (PlayerMoveEvent $event) use ($checkPoint, $player): bool {
                        return $event->getPlayer() === $player && $event->getTo()->distance($checkPoint) < 2;
                    }
                );
            }

            Main::getInstance()->getScheduler()->cancelTask($marker->getTaskId());
            $particle->remove([$player]);

            (new PlayerGoalEvent($player))->call();
        });
    }

    /**
     * セッション内のプレイヤーにのみメッセージを送信する
     *
     * @param string $message
     * @return void
     */
    private function sendMessageToSessionPlayer(string $message): void
    {
        $server = Server::getInstance();
        foreach ($this->players as $name) {
            $player = $server->getPlayer($name);
            if ($player instanceof Player) {
                $player->sendMessage($message);
            }
        }
    }
}
