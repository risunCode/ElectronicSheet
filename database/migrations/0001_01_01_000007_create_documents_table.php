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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['docx', 'xlsx', 'pptx', 'pdf'])->default('docx');
            $table->enum('status', ['draft', 'in_progress', 'under_review', 'completed', 'archived'])->default('draft');
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('file_id')->nullable()->constrained()->nullOnDelete();
            
            // Content storage
            $table->longText('content')->nullable();
            $table->json('content_json')->nullable();
            
            // Metadata
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedInteger('page_count')->default(1);
            
            // Timestamps
            $table->timestamp('last_edited_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_id');
            $table->index('status');
            $table->index('type');
            $table->index(['created_at']);
            $table->index('folder_id');
            $table->fullText(['title', 'description']);
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->longText('content')->nullable();
            $table->json('content_json')->nullable();
            $table->unsignedInteger('word_count')->default(0);
            $table->string('change_summary', 500)->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['document_id', 'version_number']);
            $table->index('document_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
    }
};
