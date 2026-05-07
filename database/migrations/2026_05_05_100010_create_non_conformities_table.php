<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('non_conformities', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('audit_assignment_id')->constrained();
            $table->text('description');
            $table->string('severity', 10)->nullable();
            $table->date('corrective_action_deadline')->nullable();
            $table->text('pu_correction')->nullable();
            $table->string('pu_correction_attachment_url', 500)->nullable();
            $table->boolean('verified_by_auditor')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('non_conformities');
    }
};
