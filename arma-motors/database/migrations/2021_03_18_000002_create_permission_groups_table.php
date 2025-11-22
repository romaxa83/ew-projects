<?php

use App\Dto\Admin\AdminDTO;
use App\Models\Admin\Admin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionGroupsTable extends Migration
{
    public function up(): void
    {
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('permission_groups');
    }
}

