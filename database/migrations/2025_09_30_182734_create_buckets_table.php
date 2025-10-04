<?php
// database/migrations/2024_01_01_000001_create_buckets_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buckets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('api_key'); // Static API key for the bucket
            $table->enum('visibility', ['private', 'public'])->default('private');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buckets');
    }
};