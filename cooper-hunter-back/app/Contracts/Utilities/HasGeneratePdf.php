<?php

namespace App\Contracts\Utilities;

use App\Dto\Utilities\Pdf\PdfDataDto;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Collection;

interface HasGeneratePdf
{
    public function getPdfDto(Collection $data, ?string $name = null): PdfDataDto;

    public function setPdfDataInCache(Collection $data, ?int $ttl = null): self;

    public function getPdfDataFromCache(string $hash): ?PdfDataDto;

    public function getPdfUrl(): string;

    public function getPdfDataHash(): string;

    public function generatePdf(PdfDataDto $pdfDataDto): PDF;
}
