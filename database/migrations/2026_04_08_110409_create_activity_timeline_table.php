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
        Schema::create('activity_timeline', function (Blueprint $table) {
            $table->id();
            //not cascadeOnDelete when parent user, or resume will be deleted for reporting
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('resume_id')->nullable()->constrained();
            $table->foreignId('resume_parse_id')->nullable()->constrained();
            $table->string('activity');
            $table->string('activity_type')->nullable();
            $table->json('details')->nullable();
            $table->boolean('is_public')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_timeline');
    }
};
