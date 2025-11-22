<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Client;
use Laravel\Passport\PersonalAccessClient;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

class PassportUpdate extends Migration
{
    public const TABLE_OAUTH_CLIENTS = 'oauth_clients';

    public function up(): void
    {
        Schema::table(
            self::TABLE_OAUTH_CLIENTS,
            function (Blueprint $table) {
                if (!Schema::hasColumn(self::TABLE_OAUTH_CLIENTS, 'provider')) {
                    $table->string('provider')
                        ->nullable()
                        ->after('secret');
                }
            }
        );

        $this->truncatePassportTables();

        Artisan::call("passport:client --password --provider=admins --name='Admins'");
        Artisan::call("passport:client --password --provider=users --name='Users'");
    }

    protected function truncatePassportTables(): void
    {
        Client::query()->truncate();
        PersonalAccessClient::query()->truncate();
        Token::query()->truncate();
        RefreshToken::query()->truncate();
    }

    public function down(): void
    {
        Schema::table(
            self::TABLE_OAUTH_CLIENTS,
            function (Blueprint $table) {
                $table->dropColumn('provider');
            }
        );
    }
}
