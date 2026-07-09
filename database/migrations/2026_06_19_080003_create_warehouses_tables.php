<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 200);
            $table->string('type', 30)->default('central');
            $table->string('location', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_available')->default(0);
            $table->integer('minimum_quantity')->default(10);
            $table->integer('maximum_quantity')->default(500);
            $table->timestamp('last_updated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['warehouse_id', 'medicine_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('stock_id')
                ->constrained()->nullOnDelete();
            $table->foreignId('destination_warehouse_id')->nullable()->after('warehouse_id')
                ->constrained('warehouses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('destination_warehouse_id');
            $table->dropConstrainedForeignId('warehouse_id');
        });
        Schema::dropIfExists('warehouse_stocks');
        Schema::dropIfExists('warehouses');
    }
};
