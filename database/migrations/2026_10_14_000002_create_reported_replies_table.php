<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reported_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reply_id')
                  ->constrained('forum_replies')
                  ->cascadeOnDelete();
            $table->foreignId('reporter_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            // Alasan laporan: spam | kata_kasar | bullying | lainnya
            $table->string('reason');
            // Status: pending | reviewed | dismissed
            $table->string('status')->default('pending');
            $table->timestamps();

            // Satu user hanya bisa melaporkan satu reply sekali
            $table->unique(['reply_id', 'reporter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reported_replies');
    }
};
