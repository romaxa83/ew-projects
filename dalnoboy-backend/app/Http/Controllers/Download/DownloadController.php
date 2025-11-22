<?php


namespace App\Http\Controllers\Download;


use App\Dto\Utilities\DownloadDto;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{

    public function xlsx(Request $request): BinaryFileResponse
    {
        /**@var DownloadDto $dto */
        $dto = $request->attributes->get('dto');

        return Excel::download(
            new ($dto->getHandler())($dto->getFileData(), $dto->getLanguage()),
            $dto->getFileName() . '.' . $dto->getFileExt()
        );
    }
}
