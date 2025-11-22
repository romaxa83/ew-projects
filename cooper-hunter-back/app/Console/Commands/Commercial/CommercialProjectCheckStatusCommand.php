<?php

namespace App\Console\Commands\Commercial;

use App\Enums\Commercial\CommercialProjectStatusEnum;
use App\Models\Commercial\CommercialProject;
use Illuminate\Console\Command;
use Throwable;

class CommercialProjectCheckStatusCommand extends Command
{
    protected $signature = 'commercial:project-check-status';

    protected $description = <<<DESCRIPTION
Проверка статусов коммерческих проектов.
Если проект за [2 недели] не был взят в работу, то ему меняется статус,
и на такой проект больше нельзя будет получить запросить доступ к Cooper&Hunter selection software
DESCRIPTION;

    public function handle(): int
    {
        $projects = CommercialProject::query()
            ->whereHas('next')
            ->whereDoesntHave('credentialsRequests')
            ->requestExpired()
            ->pending()
            ->cursor();

        foreach ($projects as $project) {
            $this->processProjectStatus($project);
        }

        return self::SUCCESS;
    }

    private function processProjectStatus(CommercialProject $project): void
    {
        try {
            makeTransaction(
                static function () use ($project) {
                    $project->status = CommercialProjectStatusEnum::CREATED();
                    $project->next->status = CommercialProjectStatusEnum::PENDING();

                    $project->push();
                }
            );
        } catch (Throwable) {
        }
    }
}
