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
        Schema::create('company_device', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            // Referencia a la tabla 'usuario'. 'usuario.id' es 'bigint unsigned'.
            $table->unsignedBigInteger('device_id');
            $table->foreign('device_id')->references('id')->on('usuario')->onDelete('cascade');

            $table->timestamps();

            // RESTRICCIÓN CLAVE: Un dispositivo solo puede pertenecer a UNA empresa.
            $table->unique(['device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_device');
    }
};
