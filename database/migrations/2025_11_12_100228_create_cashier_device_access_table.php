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
        Schema::create('cashier_device_access', function (Blueprint $table) {
            $table->id();
            // Referencia al Cajero (tabla users)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Referencia al Dispositivo (tabla usuario)
            $table->unsignedBigInteger('device_id');
            $table->foreign('device_id')->references('id')->on('usuario')->onDelete('cascade');

            $table->timestamps();

            $table->unique(['user_id', 'device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_device_access');
    }
};
