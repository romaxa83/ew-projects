<?php

namespace App\Console\Commands\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Models\GPS\Message;
use App\Models\Users\User;
use App\Services\Fueling\FuelingService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportFile extends Command
{
    protected $signature = 'fueling:import';

    public function handle(): int
    {
        $service = app(FuelingService::class);
        try {
            $user = User::query()->first();

            $args = [
                'user_id' => $user->id,
                'provider' => FuelCardProviderEnum::EFS(),
                'file' => UploadedFile::fake()->createWithContent(
                    'fueling.csv',
                    file_get_contents(\Storage::disk('tests')->path('fueling.csv'))
                )
            ];

            $service->import($args);

            return self::SUCCESS;
        } catch (\Exception $e){
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}

