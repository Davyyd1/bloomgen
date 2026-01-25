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
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('company_id')
                ->nullable();

            $table->string('original_name');
            $table->string('stored_path');

            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');

            $table->string('status')
                ->default('uploaded');

            $table->timestamps();

            // Index
            $table->index('company_id');                
            $table->index('status');                    
            $table->index(['company_id', 'status']);    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
