<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['id', 'business_id']);
            $table->unique(['business_id', 'name']);
            $table->index(['business_id', 'status', 'display_order']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('product_type')->default('standard');
            $table->string('status')->default('active');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['id', 'business_id']);
            $table->foreign(['category_id', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('categories')
                ->restrictOnDelete();
            $table->index(['business_id', 'status', 'category_id', 'display_order']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id');
            $table->string('sku', 64);
            $table->string('barcode', 64)->nullable();
            $table->string('name', 100);
            $table->decimal('sell_price', 15, 2);
            $table->decimal('default_purchase_cost', 15, 2)->nullable();
            $table->boolean('track_stock')->default(false);
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['id', 'business_id']);
            $table->unique(['business_id', 'sku']);
            $table->unique(['business_id', 'barcode']);
            $table->foreign(['product_id', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('products')
                ->cascadeOnDelete();
            $table->index(['business_id', 'product_id', 'status']);
        });

        Schema::create('outlet_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id');
            $table->foreignId('product_variant_id');
            $table->boolean('is_available')->default(true);
            $table->decimal('price_override', 15, 2)->nullable();
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
            $table->index(['business_id', 'outlet_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlet_products');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
