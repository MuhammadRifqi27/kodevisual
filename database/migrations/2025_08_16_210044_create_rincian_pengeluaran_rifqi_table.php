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
        Schema::create('rincian_pengeluaran_rifqi', function (Blueprint $table) {
            $table->id();
            $table->date('created_time')->nullable();
            $table->string('detail_expenses');
            $table->integer('cost');
            $table->unsignedBigInteger('master_category_expenses_id')->nullable();
            $table->foreign('master_category_expenses_id')->references('id')->on('master_category_expenses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rincian_pengeluaran_rifqi');
    }
};
