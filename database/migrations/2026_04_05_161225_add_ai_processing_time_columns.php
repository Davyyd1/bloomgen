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
        Schema::table('resume_parses', function (Blueprint $table) {
            $table->timestamp('processing_started_at')->nullable()->after('status');
            $table->timestamp('processing_finished_at')->nullable()->after('processing_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resume_parses', function (Blueprint $table) {
            $table->dropColumn('processing_started_at');
            $table->dropColumn('processing_finished_at');
        });
    }
};
