<?php

namespace App\Console\Commands\Init;

use App\Models\User\User;
use Illuminate\Console\Command;

class UploadFonts extends Command
{
    protected $signature = 'am:upload-fonts';

    protected $description = 'Uploads "Arial" fonts for pdf';

    /**
     * @throws \Exception
     */
    public function handle()
    {
//        php load_font.php dompdf_arial /app/public/fonts/arial/arial.ttf /app/public/fonts/arial/arial_bold.ttf /app/public/fonts/arial/arial_italic.ttf /app/public/fonts/arial/arial_bold_italic.ttf
    }
}
