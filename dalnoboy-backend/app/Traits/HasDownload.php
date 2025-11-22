<?php


namespace App\Traits;


use App\Dto\Utilities\DownloadDto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;

trait HasDownload
{
    private static string $DOWNLOAD_CACHE_PREFIX = 'download_';

    protected function getDownloadXlsxLink(
        array $fileData,
        string $fileName,
        string $handler,
        string $fileExt = 'xlsx'
    ): string {
        $dto = DownloadDto::byParam(
            [
                'file_name' => $fileName,
                'file_data' => $fileData,
                'file_ext' => $fileExt,
                'language' => Lang::getLocale(),
                'handler' => $handler
            ]
        );

        Cache::put(
            self::$DOWNLOAD_CACHE_PREFIX . $dto->getHash(),
            $dto,
            900
        );

        return route('download.xlsx', ['hash' => $dto->getHash()]);
    }

    protected function getDownloadData(string $hash): ?DownloadDto
    {
        $dto = Cache::get(self::$DOWNLOAD_CACHE_PREFIX . $hash);

        if (!$dto instanceof DownloadDto) {
            return null;
        }
        return $dto;
    }
}
