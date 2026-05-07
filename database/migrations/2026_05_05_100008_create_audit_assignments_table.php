<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('application_id')->constrained();
            $table->foreignUuid('auditor_user_id')->constrained('users');
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->text('location')->nullable();
            $table->boolean('confirmed_by_pu')->default(false);
            $table->timestamps();

            $table->index('auditor_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_assignments');
    }
};
