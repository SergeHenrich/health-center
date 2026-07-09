<?php
// 2024_01_01_000005_create_laboratory_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lab_exams', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('category', 100);
            $table->text('description')->nullable();
            $table->text('normal_range')->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('turnaround_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lab_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('doctor_id')->constrained('users');
            $table->string('request_number', 30)->unique();
            $table->timestamp('requested_at')->useCurrent();
            $table->string('status', 20)->default('pending');
            $table->string('urgency', 10)->default('normal');
            $table->text('clinical_info')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_exam_id')->constrained();
            $table->string('status', 15)->default('pending');
            $table->timestamps();
            $table->unique(['lab_request_id', 'lab_exam_id']);
        });

        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_request_item_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('lab_request_id')->constrained();
            $table->foreignId('technician_id')->constrained('users');
            $table->foreignId('validated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('result_value');
            $table->string('unit', 50)->nullable();
            $table->string('reference_range', 100)->nullable();
            $table->string('interpretation', 15)->nullable();
            $table->boolean('is_validated')->default(false);
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamp('validated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_results');
        Schema::dropIfExists('lab_request_items');
        Schema::dropIfExists('lab_requests');
        Schema::dropIfExists('lab_exams');
    }
};
