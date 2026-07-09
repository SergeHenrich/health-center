<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 200);
            $table->string('contact_name', 200)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable('Cameroun');
            $table->string('tax_id', 50)->nullable();
            $table->string('category', 50)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('supplier_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number', 50)->unique();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->text('terms')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('supplier_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('evaluation_date');
            $table->integer('quality_score')->nullable();
            $table->integer('delivery_score')->nullable();
            $table->integer('price_score')->nullable();
            $table->integer('overall_score')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('pharmacist_id')
                ->constrained()->nullOnDelete();
            $table->string('delivery_address', 255)->nullable()->after('supplier_contact');
            $table->string('payment_terms', 100)->nullable()->after('delivery_address');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropColumn(['delivery_address', 'payment_terms']);
        });
        Schema::dropIfExists('supplier_evaluations');
        Schema::dropIfExists('supplier_contracts');
        Schema::dropIfExists('suppliers');
    }
};
