<?php

namespace App\Console\Commands\Commercial;

use App\Models\Commercial\QuoteHistory;
use App\Models\Media\Media;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RemoveQuoteHistoryPdfFileCommand extends Command
{
    protected $signature = 'commercial:remove-history-pdf';

    protected $description = <<<DESCRIPTION
Сгенерированные pdf-файла в истории сметы хранятся один день,
все остальные удаляются, чтоб не засорять место на сервере,
также удаляются временный pdf-файла для предпросмотра
DESCRIPTION;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->removeHistory();
        $this->removeTmpPdf();

        return self::SUCCESS;
    }

    private function removeTmpPdf():void
    {
        Storage::deleteDirectory('pdf-preview');
        logger_info("Remove preview pdf-file");
    }

    private function removeHistory():void
    {
        $medias = Media::query()
            ->where('model_type', QuoteHistory::class)
            ->where('collection_name', QuoteHistory::MEDIA_COLLECTION_NAME)
            ->whereTime('created_at', '<', CarbonImmutable::now())
            ->get();

        $count = $medias->count();

        foreach ($medias as $item){
            /** @var $item Media */
            /** @var $history QuoteHistory */
            $history = QuoteHistory::query()->where('id', $item->model_id)->first();
            $history->clearMediaCollection(QuoteHistory::MEDIA_COLLECTION_NAME);
        }

        logger_info("Remove [{$count}] medias for history");
    }
}
