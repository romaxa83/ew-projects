<?php

namespace App\Traits\Utilities;

use App\Dto\Utilities\Pdf\PdfDataDto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Trait HasPdfService
 * @package App\Traits\Utilities
 */
trait HasPdfService
{
    private string $pdfDataHash;

    public function setPdfDataInCache(Collection $data, ?int $ttl = null, ?string $name = null): self
    {
        $pdfCatch = $this->getPdfDto($data, $name);

        Cache::put(
            config('cache.prefixes.pdf') . $pdfCatch->getHash(),
            $pdfCatch,
            $ttl ?? config('cache.ttl')
        );

        $this->pdfDataHash = $pdfCatch->getHash();

        return $this;
    }

    public function getPdfDto(Collection $data, ?string $name = null): PdfDataDto
    {
        return PdfDataDto::init($data, self::class, $name);
    }

    public function getPdfDataFromCache(string $hash): ?PdfDataDto
    {
        return Cache::get(config('cache.prefixes.pdf') . $hash);
    }

    public function getPdfDataHash(): string
    {
        return $this->pdfDataHash;
    }

    public function getPdfUrl(): string
    {
        return route('pdf.stream', $this->pdfDataHash);
    }

    public function getPdfOutput(Collection $data, ?string $name = null): string
    {
        $dto = $this->getPdfDto($data, $name);
        $pdf = $this->generatePdf($dto);

        $pdf->getDomPDF()
            ->setHttpContext(
                stream_context_create(
                    [
                        'ssl' => [
                            'allow_self_signed' => false,
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ]
                    ]
                )
            );

        $pdf->render();

        return $pdf->output();
    }
}
