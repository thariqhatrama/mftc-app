<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('pu_user_id')->constrained('users');
            $table->string('scope');
            $table->string('level');
            $table->string('status')->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('certified_at')->nullable();
            $table->string('certificate_number', 50)->unique()->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->index('pu_user_id');
            $table->index('status');
            $table->index('certificate_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
