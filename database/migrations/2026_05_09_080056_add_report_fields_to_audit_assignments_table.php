<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->text('auditor_notes')->nullable()->after('confirmed_by_pu');
            $table->string('recommendation')->nullable()->after('auditor_notes');
        });
    }

    public function down(): void
    {
        Schema::table('audit_assignments', function (Blueprint $table) {
            $table->dropColumn(['auditor_notes', 'recommendation']);
        });
    }
};
