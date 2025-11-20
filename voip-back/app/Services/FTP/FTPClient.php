<?php

namespace App\Services\FTP;

use Illuminate\Http\UploadedFile;

interface FTPClient
{
    public function upload(UploadedFile $file): bool;

    public function exist(string $fileName): bool;

    public function delete(string $fileName): bool;
}


