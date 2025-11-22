<?php

namespace App\Services\Inspections;

use App\Dto\Inspections\InspectionTirePhotoDto;
use App\Models\Inspections\InspectionTire;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class InspectionTireService
{
    public function upload(InspectionTire $model, array $data): InspectionTire
    {
        foreach ($data as $key => $file){
            /** @var $file UploadedFile*/

            $model->clearMediaCollection($key)
                ->copyMedia($file)
                ->toMediaCollection($key);
        }

        return $model->refresh();
    }

    public function uploadPhotoFromBase64(InspectionTire $model, array $data): InspectionTire
    {
        foreach ($data as $dto){
            /** @var $dto  InspectionTirePhotoDto */
            $pathStorage = Storage::disk('public')
                ->getDriver()
                ->getAdapter()
                ->getPathPrefix();

            if (!file_exists("{$pathStorage}temp")) {
                mkdir("{$pathStorage}temp", 0777, true);
            }

            $basename = $dto->fileName . '.' . $dto->fileExt;
            $filename = "{$pathStorage}temp/$basename";

            file_put_contents($filename, $dto->getDecodedFileData());

            $img = new File($filename);

            $model->clearMediaCollection($dto->type)
                ->copyMedia($img)
                ->toMediaCollection($dto->type);

            Storage::deleteDirectory('temp');
        }

        return $model->refresh();
    }
}
