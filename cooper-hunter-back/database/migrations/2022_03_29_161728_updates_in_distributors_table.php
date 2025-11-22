<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('alter table distributors modify link varchar(255) null;');
    }

    public function down(): void
    {
        DB::statement('alter table distributors modify link varchar(255) not null;');
    }
};
