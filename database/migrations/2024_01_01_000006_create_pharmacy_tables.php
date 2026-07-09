<?php
// 2024_01_01_000006_create_pharmacy_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('generic_name', 200)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('form', 20)->default('tablet');
            $table->string('strength', 100)->nullable();
            $table->string('manufacturer', 200)->nullable();
            $table->text('description')->nullable();
            $table->boolean('requires_prescription')->default(true);
            $table->boolean('is_active')->default(true);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained();
            $table->string('medicine_name', 200);
            $table->string('dosage', 100);
            $table->string('frequency', 100);
            $table->string('duration', 100)->nullable();
            $table->integer('quantity_prescribed');
            $table->integer('quantity_dispensed')->default(0);
            $table->string('route', 15)->default('oral');
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('quantity_available')->default(0);
            $table->integer('minimum_quantity')->default(10);
            $table->integer('maximum_quantity')->default(500);
            $table->timestamp('last_updated_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained();
            $table->foreignId('medicine_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('type', 15);
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->string('lot_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('moved_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('dispensations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pharmacist_id')->constrained('users');
            $table->timestamp('dispensed_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('dispensation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispensation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prescription_item_id')->constrained();
            $table->foreignId('medicine_id')->constrained();
            $table->integer('quantity_dispensed');
            $table->string('lot_number', 100)->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->foreignId('pharmacist_id')->constrained('users');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('supplier_name', 200);
            $table->string('supplier_contact', 200)->nullable();
            $table->string('status', 25)->default('draft');
            $table->timestamp('ordered_at')->useCurrent();
            $table->date('expected_delivery')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained();
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->string('lot_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('dispensation_items');
        Schema::dropIfExists('dispensations');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('medicines');
    }
};
