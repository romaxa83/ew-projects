<?php

use App\Models\Support\RequestSubjects\SupportRequestSubject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'support_request_subjects',
            static function (Blueprint $table)
            {
                $table->id();
                $table->integer('sort')
                    ->default(SupportRequestSubject::DEFAULT_SORT);
                $table->boolean('active')
                    ->default(SupportRequestSubject::DEFAULT_ACTIVE);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('support_request_subjects');
    }
};
