<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('narcotic_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained();
            $table->foreignId('dispensation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pharmacist_id')->constrained('users');
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lot_number', 100)->nullable();
            $table->integer('quantity_in')->default(0);
            $table->integer('quantity_out')->default(0);
            $table->integer('balance_after');
            $table->string('prescriber_name', 200)->nullable();
            $table->string('prescription_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('narcotic_registers');
    }
};
