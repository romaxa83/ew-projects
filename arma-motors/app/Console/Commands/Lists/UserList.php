<?php

namespace App\Console\Commands\Lists;

use App\Models\User\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;

class UserList extends Command
{
    protected $signature = 'am:list-res-user';

    protected $description = 'List all users';

    public function __construct()
    {
        parent::__construct();
    }

    private function registerCustomTableStyle()
    {
        $tableStyle = (new TableStyle())
            ->setCellHeaderFormat('<fg=black;bg=yellow>%s</>');
        Table::setStyleDefinition('secrets', $tableStyle);
    }

    public function handle()
    {

        $headers = [
            [new TableCell('A list of all users', ['colspan' => 4])],
            ['id', 'name', 'email', 'email verified at']
        ];

        $this->registerCustomTableStyle();

        $users = User::query()->with(['aaResponses'])->get();
        $data = $users->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->hasVerifiedEmail()
                    ? $user->email_verified_at->format('Y-m-d')
                    : 'Not verified',
            ];
        });

        $percentageVerified = 6;

        $data->push(new TableSeparator());
        $data->push([new TableCell(
            sprintf('%d%% verified by email', $percentageVerified),
            ['colspan' => 4]
        )]);

        $this->table($headers, $data, 'secrets');
    }

    private function otherColors()
    {
        // Default color
        $this->line('This is a line');
        // Yellow collor
        $this->warn('This is a warning');
        $this->comment('This is a comment');
        // White text on red background
        $this->error('This is an error');
        // Black text on cyan background
        $this->question('This is a question');
        // Green color
        $this->info('This is some info');


        $this->line('<bg=black> My awesome message </>');
        $this->line('<fg=green> My awesome message </>');
        $this->line('<bg=red;fg=yellow> My awesome message </>');
        $this->line('<bg=red;fg=yellow> My awesome message </>');

        $this->line("<options=bold;fg=red> MY AWESOME MESSAGE </>");
        $this->line("<options=bold;fg=red> MY AWESOME MESSAGE </>");
        $this->line("<options=underscore;bg=cyan;fg=blue> MY MESSAGE </>");
    }
}
