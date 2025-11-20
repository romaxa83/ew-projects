<?php

namespace App\Console\Commands;

use App\Models\User\IosLink;
use App\Models\User\User;
use App\Notifications\SendIosLink;
use App\Services\UserService;
use App\Traits\StoragePath;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class BulkMailingIosLink extends Command
{
    use StoragePath;

    protected $signature = 'jd:bulk-emailing';

    protected $description = 'Test email';
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(
        UserService $userService
    )
    {
        parent::__construct();
        $this->userService = $userService;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $io = IosLink::query()->where('status', true)->count();
        $this->info("Доступно ссылок - [{$io}]");

        if($this->confirm("Запустить рассылку ? [y|N]")){
            $progressBar = new ProgressBar($this->output, User::query()->count());
            $progressBar->setFormat('verbose');
            $progressBar->start();

            try {
                $count = 0;
                User::query()->orderBy('id')->chunk(50, function($users) use(&$count, $progressBar) {
                    foreach ($users as $user){
                        if($user->getRole() !== 'admin'){
//                            if(null === $user->ios_link){
                                $user = $this->userService->addIosLink($user);
                                \Notification::send($user, new SendIosLink($user));
                                $count++;
                                $progressBar->advance();
//                            }
                        }
                    }
                });

                $progressBar->finish();
                $this->info(PHP_EOL);
                $this->info("Отправлено писем - [{$count}]");
                $io = IosLink::query()->where('status', true)->count();
                $this->info("Осталось доступных ссылок - [{$io}]");
            } catch (\Exception $e){
                dd($e->getMessage());
            }
        }
    }
}
