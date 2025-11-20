<?php

namespace App\Services\FTP;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FTPSimpleClient implements FTPClient
{
    public function upload(UploadedFile $file): bool
    {
        logger_info('upload to ftp');
        return Storage::disk('ftp')
            ->putFileAs('/', $file, pretty_file_name($file->getClientOriginalName()))
            ;
    }

    public function exist(string $fileName): bool
    {
        return Storage::disk('ftp')
            ->exists('/'. $fileName)
            ;
    }

    public function delete(string $fileName): bool
    {
        return Storage::disk('ftp')
            ->delete('/'. $fileName)
            ;
    }
}

