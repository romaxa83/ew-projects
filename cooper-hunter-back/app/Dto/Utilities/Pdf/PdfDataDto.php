<?php

namespace App\Dto\Utilities\Pdf;

use App\Contracts\Utilities\HasGeneratePdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class PdfDataDto
{
    private string $name;
    private string $language;
    private string $service;
    private Collection $pdfData;
    private string $hash;

    public static function init(Collection $pdfData, string $service, ?string $name = null): self
    {
        $pdf = new self();

        $pdf->pdfData = $pdfData;
        $pdf->hash = md5($pdf->pdfData->toJson());
        $pdf->service = $service;
        $pdf->language = App::getLocale();
        $pdf->name = $name ?? config('app.name');

        return $pdf;
    }

    public function getPdfData(): Collection
    {
        return $this->pdfData;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function pdfService(): HasGeneratePdf
    {
        return resolve($this->service);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
