<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmaceutical_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pharmacist_id')->constrained('users');
            $table->timestamp('validated_at')->useCurrent();
            $table->string('status', 20)->default('approved');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pharmaceutical_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validation_id')->constrained('pharmaceutical_validations')->cascadeOnDelete();
            $table->foreignId('prescription_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('intervention_type', 30);
            $table->string('severity', 20)->default('minor');
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->string('status', 20)->default('accepted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmaceutical_interventions');
        Schema::dropIfExists('pharmaceutical_validations');
    }
};
