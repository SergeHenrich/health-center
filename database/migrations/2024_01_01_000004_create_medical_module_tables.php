<?php
// 2024_01_01_000004_create_medical_module_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users');
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('consultation_date')->useCurrent();
            $table->text('chief_complaint');
            $table->text('history_of_illness')->nullable();
            $table->text('physical_examination')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->string('status', 25)->default('open');
            $table->date('follow_up_date')->nullable();
            $table->foreignId('referred_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('referral_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nurse_id')->constrained('users');
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->integer('blood_pressure_systolic')->nullable();
            $table->integer('blood_pressure_diastolic')->nullable();
            $table->integer('heart_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->decimal('oxygen_saturation', 4, 1)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->string('icd10_code', 20)->nullable();
            $table->text('description');
            $table->string('type', 15)->default('primary');
            $table->boolean('is_chronic')->default(false);
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users');
            $table->foreignId('patient_id')->constrained();
            $table->string('prescription_number', 30)->unique();
            $table->timestamp('issued_at')->useCurrent();
            $table->date('valid_until')->nullable();
            $table->string('status', 25)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('care_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nurse_id')->constrained('users');
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hospitalization_id')->nullable();
            $table->string('care_type', 100);
            $table->text('description');
            $table->timestamp('performed_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_records');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('diagnoses');
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('consultations');
    }
};
