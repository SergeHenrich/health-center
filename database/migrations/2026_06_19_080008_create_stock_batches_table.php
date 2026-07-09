<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lot_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('quantity_available')->default(0);
            $table->integer('initial_quantity')->default(0);
            $table->string('status', 20)->default('active');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamps();

            $table->index(['medicine_id', 'warehouse_id']);
            $table->index('expiry_date');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('stock_batch_id')->nullable()->after('stock_id')
                ->constrained()->nullOnDelete();
        });

        Schema::table('dispensation_items', function (Blueprint $table) {
            $table->foreignId('stock_batch_id')->nullable()->after('medicine_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dispensation_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_batch_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_batch_id');
        });

        Schema::dropIfExists('stock_batches');
    }
};
