<?php

namespace App\Jobs\Import;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\User\IosLink;
use Illuminate\Bus\Queueable;
use App\Models\Import\IosLinkImport;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Import\Parser\IosLinkParser;

class IosLinkImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;
    private $import;

    public function __construct(string $url, IosLinkImport $import)
    {
        $this->url = $url;
        $this->import = $import;
    }

    /**
     * @throws Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        $this->import->fill([
            'status' => IosLinkImport::STATUS_IN_PROCESS,
        ])->save();

        try {
            $parser = new IosLinkParser($this->url);
            $parser->start();
            $data = $parser->getCollection();

            \DB::beginTransaction();
            $count = 0;
            foreach ($data as $datum) {
                IosLink::createFromImport([
                    'code' => Arr::get($datum->attributes, 'code', null),
                    'link' => Arr::get($datum->attributes, 'link', null)
                ]);
                $count++;
            }
            \DB::commit();
        } catch (Exception $exception) {
            \DB::rollBack();
            throw $exception;
        }

        $message = "Created count ios-links - $count";
        $this->import->fill([
            'status' => IosLinkImport::STATUS_DONE,
            'error_data' => $parser->getErrorMessage(),
            'message' => $message
        ])->save();
    }

    /**
     * @param Exception $exception
     */
    public function failed(Exception $exception): void
    {
        $this->import->update([
            'status' => IosLinkImport::STATUS_FAILED,
            'message' => Str::substr($exception->getMessage(), 0, 180),
        ]);
    }

    public function getPath()
    {
        return $this->url;
    }

    public function getImport()
    {
        return $this->import;
    }
}
