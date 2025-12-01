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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            
            // Editor preferences
            $table->string('default_font', 100)->default('Times New Roman');
            $table->unsignedInteger('default_font_size')->default(12);
            $table->boolean('auto_save')->default(true);
            $table->unsignedInteger('auto_save_interval')->default(30);
            
            // UI preferences
            $table->boolean('sidebar_collapsed')->default(false);
            $table->enum('files_view', ['grid', 'list'])->default('grid');
            $table->unsignedInteger('documents_per_page')->default(20);
            
            // Notification preferences
            $table->boolean('email_notifications')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
