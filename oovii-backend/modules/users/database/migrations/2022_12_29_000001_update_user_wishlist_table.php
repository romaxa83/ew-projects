<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Users\Enums\BonusHistoryType;

class UpdateUserWishlistTable extends Migration
{
    public function up(): void
    {
        Schema::table('user_wishlist',
            function (Blueprint $table) {
                $table->unsignedBigInteger('collection_id')->nullable();
                $table->foreign('collection_id')
                    ->references('id')
                    ->on('collections')
                    ->onDelete('cascade');
            }
        );
    }

    public function down(): void
    {
        Schema::table('user_wishlist',
            function (Blueprint $table) {
                $table->dropForeign('user_wishlist_collection_id_foreign');
                $table->dropColumn('collection_id');
            }
        );
    }
}

