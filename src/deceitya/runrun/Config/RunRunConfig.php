<?php

declare(strict_types=1);

namespace deceitya\RunRun\Config;

class RunRunConfig
{
    /** @var array */
    private $routes = [];

    public function load(string $file): void
    {
        $this->routes = json_decode(file_get_contents($file), true);
    }

    public function existsCourse(string $course): bool
    {
        return isset($this->routes[$course]);
    }

    /**
     * コースのチェックポイントのデータを取得
     *
     * @param string $cource
     * @return array|null
     */
    public function getCheckPointData(string $course): ?array
    {
        return $this->routes[$course];
    }
}
