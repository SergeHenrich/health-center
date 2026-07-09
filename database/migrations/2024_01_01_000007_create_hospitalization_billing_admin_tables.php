<?php
// 2024_01_01_000007_create_hospitalization_billing_admin_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Hospitalization ────────────────────────────────────
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 20)->unique();
            $table->string('name', 100)->nullable();
            $table->string('type', 20)->default('standard');
            $table->string('floor', 20)->nullable();
            $table->integer('capacity')->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('bed_number', 20);
            $table->string('type', 15)->default('standard');
            $table->string('status', 15)->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['room_id', 'bed_number']);
        });

        Schema::create('hospitalizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('room_id')->constrained();
            $table->foreignId('bed_id')->constrained();
            $table->foreignId('admitting_doctor_id')->constrained('users');
            $table->foreignId('attending_nurse_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('admission_number', 30)->unique();
            $table->timestamp('admission_date')->useCurrent();
            $table->timestamp('discharge_date')->nullable();
            $table->text('reason_for_admission');
            $table->string('status', 15)->default('admitted');
            $table->text('discharge_summary')->nullable();
            $table->string('discharge_condition', 15)->nullable();
            $table->timestamps();
        });

        // Add FK on care_records now that hospitalizations table exists
        Schema::table('care_records', function (Blueprint $table) {
            $table->foreign('hospitalization_id')->references('id')->on('hospitalizations')->nullOnDelete();
        });

        // ── Billing ────────────────────────────────────────────
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('created_by_id')->constrained('users');
            $table->string('invoice_number', 30)->unique();
            $table->date('invoice_date')->useCurrent();
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('invoiceable_type', 100)->nullable();
            $table->unsignedBigInteger('invoiceable_id')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description', 255);
            $table->string('item_type', 20);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by_id')->constrained('users');
            $table->string('payment_number', 30)->unique();
            $table->decimal('amount', 12, 2);
            $table->string('method', 20);
            $table->string('reference_code', 100)->nullable();
            $table->timestamp('paid_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recorded_by_id')->constrained('users');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 20);
            $table->text('description');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 20)->default('cash');
            $table->string('reference_code', 100)->nullable();
            $table->date('expense_date')->useCurrent();
            $table->string('status', 15)->default('pending');
            $table->string('receipt_path', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Administration ─────────────────────────────────────
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->text('body');
            $table->string('type', 15)->default('info');
            $table->string('channel', 10)->default('in_app');
            $table->boolean('is_broadcast')->default(false);
            $table->timestamps();
        });

        Schema::create('notification_user', function (Blueprint $table) {
            $table->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->primary(['notification_id', 'user_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 100);
            $table->string('auditable_type', 191);
            $table->unsignedBigInteger('auditable_id');
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->string('url', 500)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 10)->default('string');
            $table->string('group', 100)->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notification_user');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('hospitalizations');
        Schema::dropIfExists('beds');
        Schema::dropIfExists('rooms');
    }
};
