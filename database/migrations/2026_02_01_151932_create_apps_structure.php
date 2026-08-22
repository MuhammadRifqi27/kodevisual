<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('app_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained('apps')->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->timestamps();
        });

        Schema::create('app_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained('apps')->onDelete('cascade');
            $table->string('name');
            $table->string('code');
            $table->timestamps();
        });

        Schema::create('app_permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_role_id')->constrained('app_roles')->onDelete('cascade');
            $table->foreignId('app_permission_id')->constrained('app_permissions')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('user_app_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('app_id')->constrained('apps')->onDelete('cascade');
            $table->foreignId('app_role_id')->constrained('app_roles')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_app_roles');
        Schema::dropIfExists('app_permission_role');
        Schema::dropIfExists('app_permissions');
        Schema::dropIfExists('app_roles');
        Schema::dropIfExists('apps');
    }
};
