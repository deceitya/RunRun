<?php

declare(strict_types=1);

namespace deceitya\RunRun\Session;

use deceitya\RunRun\Event\Player\PlayerGoalEvent;
use deceitya\RunRun\Event\Session\FinishedGameEvent;
use deceitya\RunRun\Event\Session\StartGameEvent;
use deceitya\RunRun\Event\Session\StartInvitingEvent;
use deceitya\RunRun\Event\Session\StartPreparingEvent;
use deceitya\RunRun\Main;
use deceitya\RunRun\Particle\MobileTextParticle;
use Generator;
use pocketmine\event\player\PlayerEvent;
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
    /** @var string */
    private $course;

    public function __construct(string $course)
    {
        $this->course = $course;
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
     * コースをしゅとく
     *
     * @return string
     */
    public function getCourse(): string
    {
        return $this->course;
    }

    /**
     * チェックポイントを取得
     *
     * @return Position[]|Generator
     */
    public function getCheckPoints(): Generator
    {
        foreach (Main::getInstance()->getRunRunConfig()->getCheckPointData($this->course) as $position) {
            yield new Position($position['x'], $position['y'], $position['z'], Server::getInstance()->getLevelByName($position['level']));
        }
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

    public function playerflow(Player $player): void
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
                    function (PlayerMoveEvent $event) use ($checkPoint): bool {
                        return $event->getTo()->distance($checkPoint) < 2;
                    }
                );
            }

            Main::getInstance()->getScheduler()->cancelTask($marker->getTaskId());
            $player->level->addParticle($particle);

            (new PlayerGoalEvent($player))->call();
        });
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
            // プレイヤーの招待を開始
            yield listen(StartInvitingEvent::class);
            $this->phase = GameSession::PHASE_INVITING;
            Server::getInstance()->broadcastMessage('招待開始');

            // ゲームの準備段階
            yield listen(StartPreparingEvent::class);
            $this->phase = GameSession::PHASE_PREPARING;
            Server::getInstance()->broadcastMessage('準備');

            $stats = [];
            foreach ($this->players as $player) {
                $stats[$player] = 0;
            }

            // ゲームスタート
            yield listen(StartGameEvent::class);
            $this->phase = GameSession::PHASE_IN_GAME;
            Server::getInstance()->broadcastMessage('ゲーム開始');

            foreach ($stats as $player => $stat) {
                $this->playerflow(Server::getInstance()->getPlayer($player));
            }

            while (true) {
                $rank = 1;
                $event = yield listen(PlayerQuitEvent::class, PlayerGoalEvent::class);
                if ($event instanceof PlayerQuitEvent) {
                    $stats[$event->getPlayer()->getName()] = -1;
                } elseif ($event instanceof PlayerGoalEvent) {
                    $stats[$event->getPlayer()->getName()] = $rank++;
                }

                foreach ($stats as $name => $stat) {
                    if ($stat === 0) {
                        break;
                    }

                    break 2;
                }
            }

            // ゲーム終了
            //yield listen(FinishedGameEvent::class);
            $this->phase = GameSession::PHASE_END;
            Server::getInstance()->broadcastMessage('ゲーム終了');
        });
    }
}
