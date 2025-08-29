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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('pri_ape')->nullable();
            $table->string('seg_ape')->nullable();
            $table->date('fecha_nac')->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->enum('tipo_doc', ['DNI', 'CE', 'Pasaporte'])->comment('Valores: "DNI", "CE", "Pasaporte"')->nullable();
            $table->string('num_doc')->unique()->nullable();
            $table->string('ubigeo_com', 6)->nullable(); // Longitud fija de 6 caracteres
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->foreignId('discapacidad_id')->nullable()->constrained('discapacidads')->onDelete('set null');
            $table->timestamps();
            $table->foreign('ubigeo_com')
                  ->references('ubigeo_com')
                  ->on('distritos')
                  ->onDelete('restrict'); // Cambiado a restrict para mantener integridad
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
