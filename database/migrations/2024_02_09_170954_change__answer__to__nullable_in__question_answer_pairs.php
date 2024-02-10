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
        Schema::table('question_answer_pairs', function (Blueprint $table) {
            $table->longText('answer', 100000)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_answer_pairs', function (Blueprint $table) {
            $table->longText('answer', 100000)->change();
        });
    }
};
