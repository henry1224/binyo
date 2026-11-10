<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id');
            $table->foreignId('product_variant_id');
            $table->decimal('quantity_on_hand', 18, 6)->default(0);
            $table->decimal('average_cost', 18, 6)->default(0);
            $table->timestamps();

            $table->foreign(['outlet_id', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('outlets')
                ->cascadeOnDelete();
            $table->foreign(['product_variant_id', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('product_variants')
                ->cascadeOnDelete();
            $table->unique(['business_id', 'outlet_id', 'product_variant_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id');
            $table->foreignId('product_variant_id');
            $table->string('movement_type', 30);
            $table->decimal('quantity_delta', 18, 6);
            $table->decimal('unit_cost_snapshot', 18, 6);
            $table->decimal('value_delta', 24, 6);
            $table->string('source_type', 50);
            $table->unsignedBigInteger('source_id');
            // ponytail: stok awal belum memerlukan alasan; tambah reason_id saat penyesuaian stok dibuat.
            $table->timestamp('occurred_at');
            $table->foreignId('created_by');
            $table->timestamps();

            $table->foreign(['outlet_id', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('outlets')
                ->cascadeOnDelete();
            $table->foreign(['product_variant_id', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('product_variants')
                ->cascadeOnDelete();
            $table->foreign(['created_by', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('users')
                ->restrictOnDelete();
            $table->unique([
                'business_id',
                'outlet_id',
                'product_variant_id',
                'source_type',
                'source_id',
            ]);
            $table->index(['business_id', 'outlet_id', 'occurred_at']);
            $table->index(['product_variant_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_balances');
    }
};
