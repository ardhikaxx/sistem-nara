<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_ulasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->string('review_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_image')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('review_content');
            $table->dateTime('review_date')->nullable();
            $table->unsignedInteger('thumbs_up')->nullable();
            $table->text('reply_content')->nullable();
            $table->dateTime('reply_date')->nullable();
            $table->string('sentiment', 16)->nullable();
            $table->float('confidence')->nullable();
            $table->timestamps();

            $table->index(['analisis_id', 'sentiment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_ulasan');
    }
};
