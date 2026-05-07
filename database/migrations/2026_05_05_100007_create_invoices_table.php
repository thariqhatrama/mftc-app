<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('application_id')->constrained();
            $table->string('invoice_number', 50)->unique();
            $table->decimal('amount', 12, 2);
            $table->decimal('original_amount', 12, 2)->nullable();
            $table->text('override_reason')->nullable();
            $table->boolean('override_needs_approval')->default(false);
            $table->string('status')->default('pending');
            $table->string('payment_proof_url', 500)->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
