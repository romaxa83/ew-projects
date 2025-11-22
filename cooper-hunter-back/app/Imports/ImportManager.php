<?php

namespace App\Imports;

use Exception;
use Throwable;

class ImportManager
{
    private string $file;
    private string $strategyClass;
    private ImportStrategyInterface $strategy;

    public function __construct(string $pathToFile, string $strategyClass)
    {
        $this->file = $pathToFile;
        $this->strategyClass = $strategyClass;
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->import();
    }

    /**
     * @throws Throwable
     */
    private function import(): void
    {
        $this->setImportStrategy($this->getStrategy())->importSpares($this->file);
    }

    private function importSpares(string $pathToFile): void
    {
        $this->strategy->import($pathToFile);
    }

    private function setImportStrategy(ImportStrategyInterface $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * @throws Throwable
     */
    private function getStrategy(): ImportStrategyInterface
    {
        throw_unless(
            class_exists($this->strategyClass),
            Exception::class,
            "Class not exist [$this->strategyClass]"
        );

        return new $this->strategyClass();
    }
}
