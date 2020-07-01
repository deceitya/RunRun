<?php

declare(strict_types=1);

namespace deceitya\RunRun;

use deceitya\RunRun\Command\RunRunCommand;
use deceitya\RunRun\Config\RunRunConfig;
use deceitya\RunRun\Session\GameSession;
use flowy\Flowy;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase
{
    /** @var Main */
    private static $instance;

    public static function getInstance(): Main
    {
        return self::$instance;
    }

    /** @var RunRunConfig */
    private $config;
    /** @var GameSession|null */
    private $session = null;

    public function onEnable()
    {
        self::$instance = $this;

        $this->getServer()->getCommandMap()->register('RunRun', new RunRunCommand('runrun', 'RunRunコマンド'));

        $this->saveResource('route.json');
        $this->config = new RunRunConfig();
        $this->config->load($this->getDataFolder() . 'route.json');

        Flowy::bootstrap();
    }

    /**
     * 現在のセッションを返す
     *
     * @return GameSession|null
     */
    public function getSession(): ?GameSession
    {
        return $this->session;
    }

    /**
     * 新しくセッションを開いて返す
     *
     * @param string $course コース名
     *
     * @return GameSession|null
     */
    public function createNewSession(string $course): ?GameSession
    {
        if (!$this->config->existsCourse($course)) {
            return null;
        }

        $this->session = new GameSession($course);
        return $this->session;
    }

    /**
     * コンフィグ
     *
     * @return RunRunConfig
     */
    public function getRunRunConfig(): RunRunConfig
    {
        return $this->config;
    }
}
