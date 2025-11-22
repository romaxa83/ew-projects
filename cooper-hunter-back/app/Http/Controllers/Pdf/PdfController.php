<?php

namespace App\Http\Controllers\Pdf;

use App\Dto\Utilities\Pdf\PdfDataDto;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /**@var PdfDataDto $pdf */
        $pdf = $request
            ->attributes
            ->get('pdf');

        return $pdf->pdfService()
            ->generatePdf($pdf)
            ->stream(
                Str::slug($pdf->getName(), '_') . '.pdf'
            );
    }
}
