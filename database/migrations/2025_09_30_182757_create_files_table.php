<?php
// database/migrations/2024_01_01_000002_create_files_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bucket_id')->constrained()->onDelete('cascade');
            $table->string('path');
            $table->string('original_name');
            $table->string('file_name'); // Unique filename in storage
            $table->unsignedBigInteger('size');
            $table->string('mime_type');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['bucket_id', 'file_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};