<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('type', 30);
            $table->string('reference', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('file_path', 500);
            $table->string('file_name', 200);
            $table->string('file_type', 100)->nullable();
            $table->integer('file_size')->nullable();
            $table->string('version', 20)->default('1.0');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('medicine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_documents');
    }
};
