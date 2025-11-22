<?php

namespace App\Imports\Spares;

use App\Exceptions\ErrorsCode;
use App\Imports\Spares\Strategies\SparesImportStrategyInterface;
use App\Services\Telegram\TelegramDev;

class SparesImportManager
{
    private string $file;
    private string $type;
    private SparesImportStrategyInterface $strategy;

    public function __construct(string $pathToFile, string $type)
    {
        $this->file = $pathToFile;
        $this->type = $type;
    }

    public function handle()
    {
        $this->import();
    }

    private function import()
    {
        $this->setImportStrategy($this->getStrategy())->importSpares($this->file);
    }

    private function getStrategy(): SparesImportStrategyInterface
    {
        $strategyName = lcfirst($this->type) . 'Strategy';
        $strategyClass = __NAMESPACE__ . '\\Strategies\\' . ucwords($strategyName);

        throw_if(!class_exists($strategyClass), \Exception::class,
            "Класс не существует [{$strategyClass}]", ErrorsCode::IMPORT_PROBLEM);

        // @todo dev-telegram
        TelegramDev::info("Вызвана стратегия {$strategyClass}");

        return new $strategyClass;
    }

    private function setImportStrategy(SparesImportStrategyInterface $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }

    private function importSpares(string $pathToFile)
    {
        return $this->strategy->import($pathToFile);
    }
}
