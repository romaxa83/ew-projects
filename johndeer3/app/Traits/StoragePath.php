<?php

namespace App\Traits;

use App\Helpers\ReportHelper;
use Illuminate\Support\Facades\Storage;

trait StoragePath
{
    public function getStoragePath()
    {
        return Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix();
    }

    public function getPdfStoragePath()
    {
        return "{$this->getStoragePath()}pdf-report/";
    }

    public function getUrlForPdf($title)
    {
        return config('app.url') . '/storage/pdf-report/'. $title . '.pdf';
    }

    public function getNameExcelFile(): string
    {
        return 'excel/reports.xlsx';
    }

    public function getUrlForExcel($name)
    {
        return config('app.url') . '/storage/' . $name;
    }

    // проверяем существование pdf файла
    public function existPdfFile(string $titleReport) : bool
    {
        $titleReport = ReportHelper::prettyTitle($titleReport);

        return file_exists("{$this->getPdfStoragePath()}{$titleReport}.pdf");
    }

    // проверяем существование медиа файлов отчета
    public function existMediaFileReport($reportId)
    {
        return file_exists("{$this->getStoragePath()}report/{$reportId}");
    }

    // проверяем существование медиа файлов отчета
    public function existVideoReport($reportId)
    {
        return file_exists("{$this->getStoragePath()}video/{$reportId}");
    }

    public function pathToVideo($report)
    {
        if(!isset($report->video->url) || $report->video->url == null){
            throw new \Exception('No url for video');
        }

        $name = last(explode('/', $report->video->url));

        $path = "{$this->getStoragePath()}video/{$report->id}/$name";

        if(!file_exists($path)){
            throw new \Exception("No file in given path [{$path}]");
        }

        return $path;
    }

    // удаляем pdf файл
    public function deletePdfFile(string $titleReport)
    {
        $titleReport = ReportHelper::prettyTitle($titleReport);

        unlink("{$this->getPdfStoragePath()}{$titleReport}.pdf");
    }

    // удаляем медиа файлы отчета
    public function deleteMediaFileReport($reportId)
    {
        $path = "{$this->getStoragePath()}report/{$reportId}";

        array_map('unlink', glob("{$path}/*.*"));
        rmdir($path);
    }

    // удаляем видео отчета
    public function deleteVideoReport($reportId)
    {
        $path = "{$this->getStoragePath()}video/{$reportId}";

        array_map('unlink', glob("{$path}/*.*"));
        rmdir($path);
    }

    public function deleteFilesByLink(array $arrLinks)
    {
        if(!empty($arrLinks)){
            foreach ($arrLinks as $link){
                $path = "{$this->getStoragePath()}{$link}";
                if(file_exists($path)){
                    unlink($path);
                }
            }
        }
    }
}
