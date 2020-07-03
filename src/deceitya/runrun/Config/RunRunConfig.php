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

    /**
     * コースのチェックポイントのデータを取得
     *
     * @param string $course
     * @return array|null
     */
    public function getCheckPointData(string $course): ?array
    {
        if (isset($this->routes[$course])) {
            return $this->routes[$course];
        }

        return null;
    }
}
