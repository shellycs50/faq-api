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
        Schema::create('question_answer_pairs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('question', 1000);
            $table->foreignId('user_asked_id');
            $table->foreignId('language_id');
            $table->foreignId('topic_id');
            $table->foreignId('answerer_id');
            $table->longText('answer', 100000); // Store HTML from TinyMCE
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_answer_pairs');
    }
};
