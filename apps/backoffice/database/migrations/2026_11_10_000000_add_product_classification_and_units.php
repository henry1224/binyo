<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 50);
            $table->string('dimension', 20);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        $now = now();

        DB::table('units')->insert([
            ['code' => 'pcs', 'name' => 'Pcs', 'dimension' => 'count', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'porsi', 'name' => 'Porsi', 'dimension' => 'serving', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'gr', 'name' => 'Gram', 'dimension' => 'mass', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'kg', 'name' => 'Kilogram', 'dimension' => 'mass', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ml', 'name' => 'Mililiter', 'dimension' => 'volume', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'liter', 'name' => 'Liter', 'dimension' => 'volume', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'botol', 'name' => 'Botol', 'dimension' => 'container', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'karton', 'name' => 'Karton', 'dimension' => 'container', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $pieceUnitId = (int) DB::table('units')->where('code', 'pcs')->value('id');

        Schema::table('product_variants', function (Blueprint $table) use ($pieceUnitId) {
            $table->foreignId('base_unit_id')
                ->default($pieceUnitId)
                ->after('name')
                ->constrained('units')
                ->restrictOnDelete();
        });

        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->decimal('conversion_factor_to_base', 18, 6);
            $table->timestamps();

            $table->unique(['product_variant_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('base_unit_id');
        });

        Schema::dropIfExists('units');
    }
};
