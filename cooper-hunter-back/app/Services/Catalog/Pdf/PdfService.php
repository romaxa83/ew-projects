<?php

namespace App\Services\Catalog\Pdf;

use App\Models\Catalog\Pdf\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    public function create(UploadedFile $file): Pdf
    {
        $model = new Pdf();
        $model->save();

        $model->addMedia($file)
            ->toMediaCollection(Pdf::MEDIA_COLLECTION_NAME);

        $model->update([
            "path" => $model->getFirstMedia(Pdf::MEDIA_COLLECTION_NAME)->getPath()
        ]);

        return $model;
    }

    public function rewrite(Pdf $model, UploadedFile $file): Pdf
    {
        $mediaName = $model->media->first()->file_name;
        $mediaID = $model->media->first()->id;

        Storage::delete("/{$mediaID}/{$mediaName}");

        Storage::putFileAs("/{$mediaID}", $file, $mediaName);

        return $model->refresh();
    }

    public function delete(Pdf $model): bool
    {
        if($model->path && file_exists($model->path)){
            unlink($model->path);
        }

        return $model->delete();
    }
}

