<?php

namespace App\Console\Commands\Init;

use App\DTO\Admin\AdminDTO;
use App\Repositories\Admin\AdminRepository;
use App\Repositories\Passport\PassportClientRepository;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\Artisan;

class OAuthClient extends Command
{
    use ConfirmableTrait;

    protected $signature = 'am:oauth-client';

    protected $description = 'Init oauth client admin/user and write to .env file';

    public function __construct(protected PassportClientRepository $passportClientRepository)
    {
        parent::__construct();
    }

    public function handle()
    {
////        file_put_contents($this->laravel->environmentFilePath(), 'gg');
//
//
////        dd(sprintf('%s=%s', 'PUSHER_APP_ID', 'jj'));
////        dd($this->keyReplacementPattern('PUSHER_APP_ID=', ''));
////
////        file_put_contents(
////            $this->laravel->environmentFilePath(),
////            preg_replace(
////                '/PUSHER_APP_ID=*/', 'PUSHER_APP_ID=tghjfhgfhgfh',
////                file_get_contents($this->laravel->environmentFilePath())
////            ));
//
//
////        dd(preg_replace('/PUSHER_APP_ID=/', 'PUSHER_APP_ID=tet', $this->laravel->environmentFilePath()));
//
//        $this->replace_file($this->laravel->environmentFilePath(), 'apples', 'oranges');
//
//        $handle = fopen($this->laravel->environmentFilePath(), "r");
//        while (!feof($handle)) {
//            $buffer = fgets($handle, 4096);
//            dd(str_contains($buffer, 'APP_KEY'));
//            echo $buffer;
//        }
//        fclose($handle);
//
//        dd(file_get_contents($this->laravel->environmentFilePath()));
//
////        dd($this->keyReplacementPattern('PUSHER_APP_ID=', 'PUSHER_APP_ID=tet'));
//        dd(preg_replace(
//            $this->keyReplacementPattern('PUSHER_APP_ID=', 'PUSHER_APP_ID=tet'),
//            sprintf('%s=%s', 'PUSHER_APP_ID=', 'PUSHER_APP_ID=tet'),
//            file_get_contents($this->laravel->environmentFilePath())
//        ));
//
////        $app = require_once __DIR__.'/../../../.env';
//        $pathEnv = __DIR__.'/../../.env';
//
////        $file =  file_put_contents($this->laravel->environmentFilePath());
//
//        dd(file_get_contents($this->laravel->environmentFilePath()));
//
//        $user = $this->passportClientRepository->findForUser();
//        $admin = $this->passportClientRepository->findForAdmin();
//        dd($admin);

        Artisan::call("passport:client --password --provider=admins --name='Admins'");
//        Artisan::call("passport:client --password --provider=users --name='Users'");



        $this->info("[✔]");
    }

    public function replace_file($path, $string, $replace)
    {
        set_time_limit(0);

        if (is_file($path) === true)
        {
            $file = fopen($path, 'r');
            $temp = tempnam('./', 'tmp');

            if (is_resource($file) === true)
            {
                while (feof($file) === false)
                {
                    file_put_contents($temp, str_replace($string, $replace, fgets($file)), FILE_APPEND);
                }

                fclose($file);
            }

            unlink($path);
        }

        return rename($temp, $path);
    }

    protected function keyReplacementPattern($key, $value)
    {
        $escaped = preg_quote('=' . $value, '/');

        return "/^{$key}{$escaped}/m";
    }
}
