<?php

use App\Models\Employee\PbxData;
use App\Models\Zadarma\CallsEvents;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table(PbxData::TABLE, function (Blueprint $table) {
            $table->unsignedInteger('call_rec_id')
                ->nullable()
            ;
            $table->boolean('sip_status')
                ->default(false)
            ;
        });
    }

    public function down(): void
    {
        Schema::table(CallsEvents::TABLE, function (Blueprint $table) {
                $table->dropColumn('call_rec_id');
                $table->dropColumn('sip_status');
            });
    }
};
