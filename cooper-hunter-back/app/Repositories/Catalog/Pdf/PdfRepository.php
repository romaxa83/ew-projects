<?php

namespace App\Repositories\Catalog\Pdf;

use App\Models\Catalog\Pdf\Pdf;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class PdfRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Pdf::query();
    }
}
