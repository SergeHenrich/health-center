<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('atc_code', 10)->nullable()->after('code');
            $table->string('therapeutic_class', 100)->nullable()->after('category');
            $table->string('formulary_status', 20)->default('inscrit')->after('is_active');
            $table->boolean('is_narcotic')->default(false)->after('formulary_status');
            $table->boolean('is_psychotropic')->default(false)->after('is_narcotic');
            $table->boolean('is_cold_chain')->default(false)->after('is_psychotropic');
            $table->decimal('max_temperature', 4, 1)->nullable()->after('is_cold_chain');
        });

        Schema::create('therapeutic_substitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('substitute_medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('substitution_type', 30)->default('generic');
            $table->text('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['medicine_id', 'substitute_medicine_id']);
        });

        Schema::create('formulary_commission_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision', 30);
            $table->text('justification')->nullable();
            $table->date('decision_date');
            $table->date('review_date')->nullable();
            $table->string('reference_document', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formulary_commission_decisions');
        Schema::dropIfExists('therapeutic_substitutions');
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn([
                'atc_code', 'therapeutic_class', 'formulary_status',
                'is_narcotic', 'is_psychotropic', 'is_cold_chain', 'max_temperature',
            ]);
        });
    }
};
