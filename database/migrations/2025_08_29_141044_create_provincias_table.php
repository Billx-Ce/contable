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
        
        Schema::create('provincias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ubigeo_pro', 4)->unique();

            $table->string('ubigeo_dep', 2); // <-- texto, no foreignId
            $table->foreign('ubigeo_dep')
                ->references('ubigeo_dep')->on('departamentos')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provincias');
    }
};
