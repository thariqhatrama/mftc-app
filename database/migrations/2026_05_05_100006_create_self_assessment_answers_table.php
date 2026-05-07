<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('self_assessment_answers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('self_assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('question_id')->constrained('self_assessment_questions');
            $table->text('answer_value')->nullable();
            $table->jsonb('answer_files')->nullable();
            $table->timestamps();

            $table->unique(['self_assessment_id', 'question_id']);
            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('self_assessment_answers');
    }
};
