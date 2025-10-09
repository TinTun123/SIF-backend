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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('title_eng');
            $table->string('title_bur')->nullable();
            $table->date('date')->nullable();
            $table->text('organizations')->nullable(); // e.g. orgA#orgB#orgC
            $table->json('content_eng')->nullable();   // tiptap JSON output
            $table->json('content_bur')->nullable();   // tiptap JSON output
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
