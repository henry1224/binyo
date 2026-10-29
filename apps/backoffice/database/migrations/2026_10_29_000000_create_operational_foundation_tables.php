<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->string('profit_center_code', 40);
            $table->text('address')->nullable();
            $table->string('timezone')->default('Asia/Makassar');
            $table->time('business_day_start')->default('04:00');
            $table->string('receipt_prefix', 20);
            $table->boolean('prevent_negative_stock')->default(true);
            $table->boolean('replica_enabled')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['id', 'business_id']);
            $table->unique(['business_id', 'code']);
            $table->unique(['business_id', 'profit_center_code']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_system_preset')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['id', 'business_id']);
            $table->unique(['business_id', 'name']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('description');
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('permission_code');

            $table->foreign('permission_code')->references('code')->on('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_code']);
        });

        Schema::create('user_outlet_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id');
            $table->foreignId('outlet_id');
            $table->foreignId('role_id');
            $table->foreignId('assigned_by')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->foreign(['user_id', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('users')
                ->cascadeOnDelete();
            $table->foreign(['outlet_id', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('outlets')
                ->cascadeOnDelete();
            $table->foreign(['role_id', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('roles')
                ->cascadeOnDelete();
            $table->foreign(['assigned_by', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('users');
            $table->unique(['user_id', 'outlet_id', 'role_id']);
        });

        Schema::create('cash_drawers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['id', 'business_id']);
            $table->unique(['id', 'business_id', 'outlet_id']);
            $table->unique(['business_id', 'code']);
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id');
            $table->foreignId('cash_drawer_id')->nullable();
            $table->string('code', 30);
            $table->string('name');
            $table->string('platform')->default('android');
            $table->uuid('installation_id')->unique();
            $table->string('app_version')->nullable();
            $table->timestamp('trusted_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable();
            $table->timestamps();

            $table->foreign(['outlet_id', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('outlets')
                ->cascadeOnDelete();
            $table->foreign(['cash_drawer_id', 'business_id', 'outlet_id'])
                ->references(['id', 'business_id', 'outlet_id'])
                ->on('cash_drawers');
            $table->foreign(['revoked_by', 'business_id'])
                ->references(['id', 'business_id'])
                ->on('users');
            $table->unique(['business_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
        Schema::dropIfExists('cash_drawers');
        Schema::dropIfExists('user_outlet_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('outlets');
    }
};
