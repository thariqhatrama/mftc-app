<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('self_assessment_questions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('scope');
            $table->string('level');
            $table->string('category', 100)->nullable();
            $table->text('question_text');
            $table->string('input_type', 20)->default('text');
            $table->jsonb('input_options')->nullable();
            $table->text('helper_text')->nullable();
            $table->boolean('is_required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('has_answers')->default(false);
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['scope', 'level', 'is_active', 'sort_order']);
            $table->unique(['scope', 'level', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('self_assessment_questions');
    }
};
