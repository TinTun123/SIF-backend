<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('music', function (Blueprint $table) {
            //
            $table->string('thumbnail')->nullable()->after('file_url');
            $table->string('fb_video_id')->nullable()->after('thumbnail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('music', function (Blueprint $table) {
            //
            $table->dropColumn('thumbnail');
            $table->dropColumn('fb_video_id');
        });
    }
};
