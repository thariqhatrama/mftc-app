<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_checklists', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('audit_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('site_id')->nullable()->constrained('business_sites');
            $table->string('criteria_id', 50)->nullable();
            $table->text('criteria_description')->nullable();
            $table->string('result')->nullable();
            $table->text('auditor_note')->nullable();
            $table->text('corrective_action_required')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_checklists');
    }
};
