<?php


namespace App\Console\Commands;


use App\Models\Translates\Translate;
use Illuminate\Console\Command;

class AddLanguage extends Command
{
    protected $signature = 'add-language {language}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add-language';

    public function handle(): int
    {
        $language = $this->argument('language');

        $translates = Translate::all();

        $translates
            ->map(
                fn (Translate $translate) => $translate
                    ->data()
                    ->firstOrCreate(
                        [
                            'language' => $language
                        ]
                    )
            );

        return self::SUCCESS;
    }
}
